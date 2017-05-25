<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Mail;

use PSX\Framework\Config\Config;

/**
 * TransportFactory
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class TransportFactory
{
    /**
     * @param \PSX\Framework\Config\Config $config
     * @return \Swift_Transport
     */
    public static function createTransport(Config $config)
    {
        if ($config['psx_debug'] === false) {
            $mailer = $config['fusio_mailer'];
            if (!empty($mailer)) {
                if ($mailer['transport'] == 'smtp') {
                    $transport = \Swift_SmtpTransport::newInstance($mailer['host'], $mailer['port']);
                    if (isset($mailer['encryption'])) {
                        $transport->setEncryption($mailer['encryption']);
                    }
                    if (isset($mailer['username'])) {
                        $transport->setUsername($mailer['username']);
                        $transport->setPassword($mailer['password']);
                    }
                    return $transport;
                }
            }

            return \Swift_MailTransport::newInstance();
        } else {
            return \Swift_NullTransport::newInstance();
        }
    }
}
