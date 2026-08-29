<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Fusio\Impl\Tests\Backend\Api\Config;

use Fusio\Impl\Tests\DbTestCase;
use Fusio\Impl\Tests\Normalizer;

/**
 * CollectionTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class CollectionTest extends DbTestCase
{
    public function testGet(): void
    {
        $response = $this->sendRequest('/backend/config', 'GET', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ]);

        $body = (string) $response->getBody();
        $body = Normalizer::normalize($body);

        $expect = <<<'JSON'
{
    "kind": "[kind]",
    "totalResults": 39,
    "startIndex": 0,
    "itemsPerPage": 16,
    "entry": [
        {
            "kind": "[kind]",
            "id": 1,
            "type": 2,
            "name": "app_approval",
            "description": "If true the status of a new app is PENDING so that an administrator has to manually activate the app",
            "value": false
        },
        {
            "kind": "[kind]",
            "id": 5,
            "type": 1,
            "name": "authorization_url",
            "description": "Url where the user can authorize for the OAuth2 flow",
            "value": ""
        },
        {
            "kind": "[kind]",
            "id": 21,
            "type": 1,
            "name": "captcha_key",
            "description": "Captcha Key",
            "value": ""
        },
        {
            "kind": "[kind]",
            "id": 22,
            "type": 1,
            "name": "captcha_secret",
            "description": "Captcha Secret",
            "value": ""
        },
        {
            "kind": "[kind]",
            "id": 20,
            "type": 1,
            "name": "captcha_type",
            "description": "Captcha Type, must be one of: friendly, hcaptcha, recaptcha or fusio",
            "value": "fusio"
        },
        {
            "kind": "[kind]",
            "id": 2,
            "type": 3,
            "name": "consumer_max_apps",
            "description": "The max amount of apps a consumer can generate",
            "value": 16
        },
        {
            "kind": "[kind]",
            "id": 3,
            "type": 3,
            "name": "consumer_max_tokens",
            "description": "The max amount of tokens a consumer can generate",
            "value": 16
        },
        {
            "kind": "[kind]",
            "id": 4,
            "type": 3,
            "name": "consumer_max_webhooks",
            "description": "The max amount of webhooks a consumer can register",
            "value": 8
        },
        {
            "kind": "[kind]",
            "id": 11,
            "type": 1,
            "name": "info_contact_email",
            "description": "The email address of the contact person\/organization. MUST be in the format of an email address",
            "value": ""
        },
        {
            "kind": "[kind]",
            "id": 9,
            "type": 1,
            "name": "info_contact_name",
            "description": "The identifying name of the contact person\/organization",
            "value": ""
        },
        {
            "kind": "[kind]",
            "id": 10,
            "type": 1,
            "name": "info_contact_url",
            "description": "The URL pointing to the contact information. MUST be in the format of a URL",
            "value": ""
        },
        {
            "kind": "[kind]",
            "id": 7,
            "type": 1,
            "name": "info_description",
            "description": "A short description of the application. CommonMark syntax MAY be used for rich text representation",
            "value": "Self-Hosted API Management for Builders."
        },
        {
            "kind": "[kind]",
            "id": 12,
            "type": 1,
            "name": "info_license_name",
            "description": "The license name used for the API",
            "value": ""
        },
        {
            "kind": "[kind]",
            "id": 13,
            "type": 1,
            "name": "info_license_url",
            "description": "A URL to the license used for the API. MUST be in the format of a URL",
            "value": ""
        },
        {
            "kind": "[kind]",
            "id": 6,
            "type": 1,
            "name": "info_title",
            "description": "The title of the application",
            "value": "Fusio"
        },
        {
            "kind": "[kind]",
            "id": 8,
            "type": 1,
            "name": "info_tos",
            "description": "A URL to the Terms of Service for the API. MUST be in the format of a URL",
            "value": ""
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetSearch(): void
    {
        $response = $this->sendRequest('/backend/config?search=register_subject', 'GET', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ]);

        $body = (string) $response->getBody();
        $body = Normalizer::normalize($body);

        $expect = <<<'JSON'
{
    "kind": "[kind]",
    "totalResults": 1,
    "startIndex": 0,
    "itemsPerPage": 16,
    "entry": [
        {
            "kind": "[kind]",
            "id": 14,
            "type": 1,
            "name": "mail_register_subject",
            "description": "Subject of the activation mail",
            "value": "Fusio registration"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetCount(): void
    {
        $response = $this->sendRequest('/backend/config?count=80', 'GET', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ]);

        $body = (string) $response->getBody();
        $body = Normalizer::normalize($body);

        $expect = <<<'JSON'
{
    "kind": "[kind]",
    "totalResults": 39,
    "startIndex": 0,
    "itemsPerPage": 80,
    "entry": [
        {
            "kind": "[kind]",
            "id": 1,
            "type": 2,
            "name": "app_approval",
            "description": "If true the status of a new app is PENDING so that an administrator has to manually activate the app",
            "value": false
        },
        {
            "kind": "[kind]",
            "id": 5,
            "type": 1,
            "name": "authorization_url",
            "description": "Url where the user can authorize for the OAuth2 flow",
            "value": ""
        },
        {
            "kind": "[kind]",
            "id": 21,
            "type": 1,
            "name": "captcha_key",
            "description": "Captcha Key",
            "value": ""
        },
        {
            "kind": "[kind]",
            "id": 22,
            "type": 1,
            "name": "captcha_secret",
            "description": "Captcha Secret",
            "value": ""
        },
        {
            "kind": "[kind]",
            "id": 20,
            "type": 1,
            "name": "captcha_type",
            "description": "Captcha Type, must be one of: friendly, hcaptcha, recaptcha or fusio",
            "value": "fusio"
        },
        {
            "kind": "[kind]",
            "id": 2,
            "type": 3,
            "name": "consumer_max_apps",
            "description": "The max amount of apps a consumer can generate",
            "value": 16
        },
        {
            "kind": "[kind]",
            "id": 3,
            "type": 3,
            "name": "consumer_max_tokens",
            "description": "The max amount of tokens a consumer can generate",
            "value": 16
        },
        {
            "kind": "[kind]",
            "id": 4,
            "type": 3,
            "name": "consumer_max_webhooks",
            "description": "The max amount of webhooks a consumer can register",
            "value": 8
        },
        {
            "kind": "[kind]",
            "id": 11,
            "type": 1,
            "name": "info_contact_email",
            "description": "The email address of the contact person\/organization. MUST be in the format of an email address",
            "value": ""
        },
        {
            "kind": "[kind]",
            "id": 9,
            "type": 1,
            "name": "info_contact_name",
            "description": "The identifying name of the contact person\/organization",
            "value": ""
        },
        {
            "kind": "[kind]",
            "id": 10,
            "type": 1,
            "name": "info_contact_url",
            "description": "The URL pointing to the contact information. MUST be in the format of a URL",
            "value": ""
        },
        {
            "kind": "[kind]",
            "id": 7,
            "type": 1,
            "name": "info_description",
            "description": "A short description of the application. CommonMark syntax MAY be used for rich text representation",
            "value": "Self-Hosted API Management for Builders."
        },
        {
            "kind": "[kind]",
            "id": 12,
            "type": 1,
            "name": "info_license_name",
            "description": "The license name used for the API",
            "value": ""
        },
        {
            "kind": "[kind]",
            "id": 13,
            "type": 1,
            "name": "info_license_url",
            "description": "A URL to the license used for the API. MUST be in the format of a URL",
            "value": ""
        },
        {
            "kind": "[kind]",
            "id": 6,
            "type": 1,
            "name": "info_title",
            "description": "The title of the application",
            "value": "Fusio"
        },
        {
            "kind": "[kind]",
            "id": 8,
            "type": 1,
            "name": "info_tos",
            "description": "A URL to the Terms of Service for the API. MUST be in the format of a URL",
            "value": ""
        },
        {
            "kind": "[kind]",
            "id": 19,
            "type": 6,
            "name": "mail_points_body",
            "description": "Body of the points threshold mail",
            "value": "Hello {name},\n\nyour account has reached the configured threshold of {points} points.\nIf your account reaches 0 points your are not longer able to invoke specific endpoints.\nTo prevent this please go to the developer portal to purchase new points:\n{apps_url}\/developer"
        },
        {
            "kind": "[kind]",
            "id": 18,
            "type": 1,
            "name": "mail_points_subject",
            "description": "Subject of the points threshold mail",
            "value": "Fusio points threshold reached"
        },
        {
            "kind": "[kind]",
            "id": 17,
            "type": 6,
            "name": "mail_pw_reset_body",
            "description": "Body of the password reset mail",
            "value": "Hello {name},\n\nyou have requested to reset your password.\nTo set a new password please visit the following link:\n{apps_url}\/developer\/password\/confirm\/{token}\n\nPlease ignore this email if you have not requested a password reset."
        },
        {
            "kind": "[kind]",
            "id": 16,
            "type": 1,
            "name": "mail_pw_reset_subject",
            "description": "Subject of the password reset mail",
            "value": "Fusio password reset"
        },
        {
            "kind": "[kind]",
            "id": 15,
            "type": 6,
            "name": "mail_register_body",
            "description": "Body of the activation mail",
            "value": "Hello {name},\n\nyou have successful registered at Fusio.\nTo activate you account please visit the following link:\n{apps_url}\/developer\/register\/activate\/{token}"
        },
        {
            "kind": "[kind]",
            "id": 14,
            "type": 1,
            "name": "mail_register_subject",
            "description": "Subject of the activation mail",
            "value": "Fusio registration"
        },
        {
            "kind": "[kind]",
            "id": 33,
            "type": 1,
            "name": "marketplace_client_id",
            "description": "Marketplace Client-Id, this is either your username or app key of the Fusio marketplace (marketplace.fusio-project.org)",
            "value": ""
        },
        {
            "kind": "[kind]",
            "id": 34,
            "type": 1,
            "name": "marketplace_client_secret",
            "description": "Marketplace Client-Secret, this is either your password or app secret of the Fusio marketplace (marketplace.fusio-project.org)",
            "value": ""
        },
        {
            "kind": "[kind]",
            "id": 25,
            "type": 1,
            "name": "payment_currency",
            "description": "The three-character ISO-4217 currency code which is used to process payments",
            "value": ""
        },
        {
            "kind": "[kind]",
            "id": 24,
            "type": 1,
            "name": "payment_stripe_portal_configuration",
            "description": "The stripe portal configuration id",
            "value": ""
        },
        {
            "kind": "[kind]",
            "id": 23,
            "type": 1,
            "name": "payment_stripe_secret",
            "description": "The stripe webhook secret which is needed to verify a webhook request",
            "value": ""
        },
        {
            "kind": "[kind]",
            "id": 27,
            "type": 3,
            "name": "points_default",
            "description": "The default amount of points which a user receives if he registers",
            "value": 0
        },
        {
            "kind": "[kind]",
            "id": 28,
            "type": 3,
            "name": "points_threshold",
            "description": "If a user goes below this points threshold we send an information to the user",
            "value": 0
        },
        {
            "kind": "[kind]",
            "id": 26,
            "type": 1,
            "name": "role_default",
            "description": "Default role which a user gets assigned on registration",
            "value": "Consumer"
        },
        {
            "kind": "[kind]",
            "id": 35,
            "type": 1,
            "name": "sdkgen_client_id",
            "description": "SDKgen Client-Id, this is either your username or app key of the SDKgen app (sdkgen.app)",
            "value": ""
        },
        {
            "kind": "[kind]",
            "id": 36,
            "type": 1,
            "name": "sdkgen_client_secret",
            "description": "SDKgen Client-Secret, this is either your password or app secret of the SDKgen app (sdkgen.app)",
            "value": ""
        },
        {
            "kind": "[kind]",
            "id": 30,
            "type": 1,
            "name": "system_dispatcher",
            "description": "Optional the name of an HTTP or Message-Queue connection which is used to dispatch events. By default the system uses simply cron and an internal table to dispatch such events, for better performance you can provide a Message-Queue connection and Fusio will only dispatch the event to the queue, then your worker must execute the actual webhook HTTP request",
            "value": ""
        },
        {
            "kind": "[kind]",
            "id": 29,
            "type": 1,
            "name": "system_mailer",
            "description": "Optional the name of an SMTP connection which is used as mailer, by default the system uses the connection configured through the APP_MAILER environment variable",
            "value": ""
        },
        {
            "kind": "[kind]",
            "id": 37,
            "type": 1,
            "name": "typehub_client_id",
            "description": "TypeHub Client-Id, this is either your username or app key of the TypeHub app (typehub.cloud)",
            "value": "client_id"
        },
        {
            "kind": "[kind]",
            "id": 38,
            "type": 1,
            "name": "typehub_client_secret",
            "description": "TypeHub Client-Secret, this is either your password or app secret of the TypeHub app (typehub.cloud)",
            "value": "client_secret"
        },
        {
            "kind": "[kind]",
            "id": 39,
            "type": 1,
            "name": "typehub_document_name",
            "description": "Name of the TypeHub document under which the specification gets published",
            "value": "document_name"
        },
        {
            "kind": "[kind]",
            "id": 32,
            "type": 2,
            "name": "user_approval",
            "description": "Whether the user needs to activate the account through an email",
            "value": true
        },
        {
            "kind": "[kind]",
            "id": 31,
            "type": 3,
            "name": "user_pw_length",
            "description": "Minimal required password length",
            "value": 8
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost(): void
    {
        $response = $this->sendRequest('/backend/config', 'POST', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ], json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testPut(): void
    {
        $response = $this->sendRequest('/backend/config', 'PUT', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ], json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testDelete(): void
    {
        $response = $this->sendRequest('/backend/config', 'DELETE', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ], json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }
}
