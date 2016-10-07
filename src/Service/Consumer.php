<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Fusio\Impl\Service;

use DateInterval;
use Firebase\JWT\JWT;
use Fusio\Impl\Mail\MailerInterface;
use Fusio\Impl\Service\Consumer\Model\User as ModelUser;
use Fusio\Impl\Service\Consumer\ProviderInterface;
use Fusio\Impl\Table;
use PSX\Framework\Config\Config as PSXConfig;
use PSX\Http;
use PSX\Http\Exception as StatusCode;
use PSX\Json\Parser;
use PSX\Sql\Condition;

/**
 * Consumer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Consumer
{
    /**
     * @var \Fusio\Impl\Service\User
     */
    protected $user;

    /**
     * @var \Fusio\Impl\Service\App
     */
    protected $app;

    /**
     * @var \Fusio\Impl\Service\Config
     */
    protected $config;

    /**
     * @var \PSX\Http\Client
     */
    protected $httpClient;

    /**
     * @var \Fusio\Impl\Mail\MailerInterface
     */
    protected $mailer;

    /**
     * @var \PSX\Framework\Config\Config
     */
    protected $psxConfig;

    public function __construct(User $user, App $app, Config $config, Http\Client $httpClient, MailerInterface $mailer, PSXConfig $psxConfig)
    {
        $this->user       = $user;
        $this->app        = $app;
        $this->config     = $config;
        $this->httpClient = $httpClient;
        $this->mailer     = $mailer;
        $this->psxConfig  = $psxConfig;
    }

    public function login($name, $password)
    {
        $userId = $this->user->authenticateUser($name, $password, [Table\User::STATUS_ADMINISTRATOR, Table\User::STATUS_CONSUMER]);
        if ($userId > 0) {
            return $this->createToken($userId, $this->user->getAvailableScopes($userId));
        }

        return null;
    }
    
    public function register($name, $email, $password, $captcha)
    {
        // verify captcha if secret is available
        $secret = $this->config->getValue('recaptcha_secret');
        if (!empty($secret)) {
            $this->verifyCaptcha($captcha, $secret);
        }

        $scopes = $this->getDefaultScopes();
        $userId = $this->user->create(
            Table\User::STATUS_DISABLED,
            $name,
            $email,
            $password,
            $scopes
        );

        // send activation mail
        $this->sendActivationMail($userId, $name, $email);
    }

    public function activate($token)
    {
        $payload = JWT::decode($token, $this->psxConfig->get('fusio_project_key'), ['HS256']);
        $userId  = isset($payload->sub) ? $payload->sub : null;
        $expires = isset($payload->exp) ? $payload->exp : null;

        if (time() < $expires) {
            $this->user->changeStatus($userId, Table\User::STATUS_CONSUMER);
        } else {
            throw new StatusCode\BadRequestException('Token is expired');
        }
    }

    public function provider($providerName, $code, $clientId, $redirectUri)
    {
        $providerName = strtolower($providerName);
        $provider     = $this->getProvider($providerName);

        if ($provider instanceof ProviderInterface) {
            $user = $provider->requestUser($code, $clientId, $redirectUri);

            if ($user instanceof ModelUser) {
                $scopes = $this->getDefaultScopes();
                $userId = $this->user->createRemote(
                    $provider->getId(),
                    $user->getId(),
                    $user->getName(),
                    $user->getEmail(),
                    $scopes
                );

                return $this->createToken($userId, $scopes);
            } else {
                throw new StatusCode\BadRequestException('Could not request user informations');
            }
        } else {
            throw new StatusCode\BadRequestException('Not supported provider');
        }
    }

    protected function createToken($userId, array $scopes)
    {
        // @TODO this is the consumer app. Probably we need a better way to
        // define this id
        $appId = 2;

        $token = $this->app->generateAccessToken(
            $appId,
            $userId,
            $scopes,
            isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
            new DateInterval($this->psxConfig->get('fusio_expire_consumer'))
        );

        $user = $this->user->get($userId);

        $payload = [
            'sub'  => $token->getAccessToken(),
            'iat'  => time(),
            'exp'  => $token->getExpiresIn(),
            'name' => $user['name']
        ];

        return JWT::encode($payload, $this->psxConfig->get('fusio_project_key'));
    }

    protected function getProvider($provider)
    {
        switch ($provider) {
            case 'facebook':
                $secret = $this->config->getValue('provider_facebook_secret');
                if (!empty($secret)) {
                    return new Consumer\Provider\Facebook($this->httpClient, $secret);
                }
                break;
            case 'github':
                $secret = $this->config->getValue('provider_github_secret');
                if (!empty($secret)) {
                    return new Consumer\Provider\Github($this->httpClient, $secret);
                }
                break;
            case 'google':
                $secret = $this->config->getValue('provider_google_secret');
                if (!empty($secret)) {
                    return new Consumer\Provider\Google($this->httpClient, $secret);
                }
                break;
        }

        return null;
    }

    protected function getDefaultScopes()
    {
        $scopes = $this->config->getValue('scopes_default');

        return array_filter(array_map('trim', explode(',', $scopes)), function ($scope) {
            // we filter out the backend scope since this would be a major
            // security issue
            return !empty($scope) && $scope != 'backend';
        });
    }

    protected function sendActivationMail($userId, $name, $email)
    {
        $payload = [
            'sub' => $userId,
            'exp' => time() + (60 * 60),
        ];

        $token   = JWT::encode($payload, $this->psxConfig->get('fusio_project_key'));
        $subject = $this->config->getValue('mail_register_subject');
        $body    = $this->config->getValue('mail_register_body');

        $values = array(
            'name'  => $name,
            'email' => $email,
            'token' => $token,
        );

        foreach ($values as $key => $value) {
            $body = str_replace($key, $value, $body);
        }

        $this->mailer->send($subject, [$email], $body);
    }

    protected function verifyCaptcha($captcha, $secret)
    {
        $request = new Http\PostRequest('https://www.google.com/recaptcha/api/siteverify', [], [
            'secret'   => $secret,
            'response' => $captcha,
            'remoteip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
        ]);

        $response = $this->httpClient->request($request);
        
        if ($response->getStatusCode() == 200) {
            $data = Parser::decode($response->getBody());
            if ($data->success === true) {
                return true;
            }
        }

        throw new StatusCode\BadRequestException('Invalid captcha');
    }
}
