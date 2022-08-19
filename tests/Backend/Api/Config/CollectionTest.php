<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Tests\Backend\Api\Config;

use Fusio\Impl\Tests\Documentation;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * CollectionTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class CollectionTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testDocumentation()
    {
        $response = $this->sendRequest('/system/doc/*/backend/config', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = Documentation::getResource($response);
        $expect = file_get_contents(__DIR__ . '/resource/collection.json');

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/config', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "totalResults": 35,
    "startIndex": 0,
    "itemsPerPage": 16,
    "entry": [
        {
            "id": 1,
            "type": 2,
            "name": "app_approval",
            "description": "If true the status of a new app is PENDING so that an administrator has to manually activate the app",
            "value": false
        },
        {
            "id": 2,
            "type": 3,
            "name": "app_consumer",
            "description": "The max amount of apps a consumer can register",
            "value": 16
        },
        {
            "id": 3,
            "type": 1,
            "name": "authorization_url",
            "description": "Url where the user can authorize for the OAuth2 flow",
            "value": ""
        },
        {
            "id": 4,
            "type": 3,
            "name": "consumer_subscription",
            "description": "The max amount of subscriptions a consumer can add",
            "value": 8
        },
        {
            "id": 10,
            "type": 1,
            "name": "info_contact_email",
            "description": "The email address of the contact person\/organization. MUST be in the format of an email address",
            "value": ""
        },
        {
            "id": 8,
            "type": 1,
            "name": "info_contact_name",
            "description": "The identifying name of the contact person\/organization",
            "value": ""
        },
        {
            "id": 9,
            "type": 1,
            "name": "info_contact_url",
            "description": "The URL pointing to the contact information. MUST be in the format of a URL",
            "value": ""
        },
        {
            "id": 6,
            "type": 1,
            "name": "info_description",
            "description": "A short description of the application. CommonMark syntax MAY be used for rich text representation",
            "value": ""
        },
        {
            "id": 11,
            "type": 1,
            "name": "info_license_name",
            "description": "The license name used for the API",
            "value": ""
        },
        {
            "id": 12,
            "type": 1,
            "name": "info_license_url",
            "description": "A URL to the license used for the API. MUST be in the format of a URL",
            "value": ""
        },
        {
            "id": 5,
            "type": 1,
            "name": "info_title",
            "description": "The title of the application",
            "value": "Fusio"
        },
        {
            "id": 7,
            "type": 1,
            "name": "info_tos",
            "description": "A URL to the Terms of Service for the API. MUST be in the format of a URL",
            "value": ""
        },
        {
            "id": 18,
            "type": 6,
            "name": "mail_points_body",
            "description": "Body of the points threshold mail",
            "value": "Hello {name},\n\nyour account has reached the configured threshold of {points} points.\nIf your account reaches 0 points your are not longer able to invoke specific endpoints.\nTo prevent this please go to the developer portal to purchase new points:\n{apps_url}\/developer"
        },
        {
            "id": 17,
            "type": 1,
            "name": "mail_points_subject",
            "description": "Subject of the points threshold mail",
            "value": "Fusio points threshold reached"
        },
        {
            "id": 16,
            "type": 6,
            "name": "mail_pw_reset_body",
            "description": "Body of the password reset mail",
            "value": "Hello {name},\n\nyou have requested to reset your password.\nTo set a new password please visit the following link:\n{apps_url}\/developer\/password\/confirm\/{token}\n\nPlease ignore this email if you have not requested a password reset."
        },
        {
            "id": 15,
            "type": 1,
            "name": "mail_pw_reset_subject",
            "description": "Subject of the password reset mail",
            "value": "Fusio password reset"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $actual);
        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGetSearch()
    {
        $response = $this->sendRequest('/backend/config?search=register_subject', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "totalResults": 1,
    "startIndex": 0,
    "itemsPerPage": 16,
    "entry": [
        {
            "id": 13,
            "type": 1,
            "name": "mail_register_subject",
            "description": "Subject of the activation mail",
            "value": "Fusio registration"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $actual);
        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGetCount()
    {
        $response = $this->sendRequest('/backend/config?count=80', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "totalResults": 35,
    "startIndex": 0,
    "itemsPerPage": 80,
    "entry": [
        {
            "id": 1,
            "type": 2,
            "name": "app_approval",
            "description": "If true the status of a new app is PENDING so that an administrator has to manually activate the app",
            "value": false
        },
        {
            "id": 2,
            "type": 3,
            "name": "app_consumer",
            "description": "The max amount of apps a consumer can register",
            "value": 16
        },
        {
            "id": 3,
            "type": 1,
            "name": "authorization_url",
            "description": "Url where the user can authorize for the OAuth2 flow",
            "value": ""
        },
        {
            "id": 4,
            "type": 3,
            "name": "consumer_subscription",
            "description": "The max amount of subscriptions a consumer can add",
            "value": 8
        },
        {
            "id": 10,
            "type": 1,
            "name": "info_contact_email",
            "description": "The email address of the contact person\/organization. MUST be in the format of an email address",
            "value": ""
        },
        {
            "id": 8,
            "type": 1,
            "name": "info_contact_name",
            "description": "The identifying name of the contact person\/organization",
            "value": ""
        },
        {
            "id": 9,
            "type": 1,
            "name": "info_contact_url",
            "description": "The URL pointing to the contact information. MUST be in the format of a URL",
            "value": ""
        },
        {
            "id": 6,
            "type": 1,
            "name": "info_description",
            "description": "A short description of the application. CommonMark syntax MAY be used for rich text representation",
            "value": ""
        },
        {
            "id": 11,
            "type": 1,
            "name": "info_license_name",
            "description": "The license name used for the API",
            "value": ""
        },
        {
            "id": 12,
            "type": 1,
            "name": "info_license_url",
            "description": "A URL to the license used for the API. MUST be in the format of a URL",
            "value": ""
        },
        {
            "id": 5,
            "type": 1,
            "name": "info_title",
            "description": "The title of the application",
            "value": "Fusio"
        },
        {
            "id": 7,
            "type": 1,
            "name": "info_tos",
            "description": "A URL to the Terms of Service for the API. MUST be in the format of a URL",
            "value": ""
        },
        {
            "id": 18,
            "type": 6,
            "name": "mail_points_body",
            "description": "Body of the points threshold mail",
            "value": "Hello {name},\n\nyour account has reached the configured threshold of {points} points.\nIf your account reaches 0 points your are not longer able to invoke specific endpoints.\nTo prevent this please go to the developer portal to purchase new points:\n{apps_url}\/developer"
        },
        {
            "id": 17,
            "type": 1,
            "name": "mail_points_subject",
            "description": "Subject of the points threshold mail",
            "value": "Fusio points threshold reached"
        },
        {
            "id": 16,
            "type": 6,
            "name": "mail_pw_reset_body",
            "description": "Body of the password reset mail",
            "value": "Hello {name},\n\nyou have requested to reset your password.\nTo set a new password please visit the following link:\n{apps_url}\/developer\/password\/confirm\/{token}\n\nPlease ignore this email if you have not requested a password reset."
        },
        {
            "id": 15,
            "type": 1,
            "name": "mail_pw_reset_subject",
            "description": "Subject of the password reset mail",
            "value": "Fusio password reset"
        },
        {
            "id": 14,
            "type": 6,
            "name": "mail_register_body",
            "description": "Body of the activation mail",
            "value": "Hello {name},\n\nyou have successful registered at Fusio.\nTo activate you account please visit the following link:\n{apps_url}\/developer\/register\/activate\/{token}"
        },
        {
            "id": 13,
            "type": 1,
            "name": "mail_register_subject",
            "description": "Subject of the activation mail",
            "value": "Fusio registration"
        },
        {
            "id": 28,
            "type": 1,
            "name": "payment_currency",
            "description": "The three-character ISO-4217 currency code which is used to process payments",
            "value": ""
        },
        {
            "id": 27,
            "type": 1,
            "name": "payment_stripe_secret",
            "description": "The stripe webhook secret which is needed to verify a webhook request",
            "value": ""
        },
        {
            "id": 30,
            "type": 3,
            "name": "points_default",
            "description": "The default amount of points which a user receives if he registers",
            "value": 0
        },
        {
            "id": 31,
            "type": 3,
            "name": "points_threshold",
            "description": "If a user goes below this points threshold we send an information to the user",
            "value": 0
        },
        {
            "id": 19,
            "type": 1,
            "name": "provider_facebook_key",
            "description": "Facebook app key",
            "value": ""
        },
        {
            "id": 20,
            "type": 1,
            "name": "provider_facebook_secret",
            "description": "Facebook app secret",
            "value": ""
        },
        {
            "id": 23,
            "type": 1,
            "name": "provider_github_key",
            "description": "GitHub app key",
            "value": ""
        },
        {
            "id": 24,
            "type": 1,
            "name": "provider_github_secret",
            "description": "GitHub app secret",
            "value": ""
        },
        {
            "id": 21,
            "type": 1,
            "name": "provider_google_key",
            "description": "Google app key",
            "value": ""
        },
        {
            "id": 22,
            "type": 1,
            "name": "provider_google_secret",
            "description": "Google app secret",
            "value": ""
        },
        {
            "id": 25,
            "type": 1,
            "name": "recaptcha_key",
            "description": "ReCaptcha key",
            "value": ""
        },
        {
            "id": 26,
            "type": 1,
            "name": "recaptcha_secret",
            "description": "ReCaptcha secret",
            "value": ""
        },
        {
            "id": 29,
            "type": 1,
            "name": "role_default",
            "description": "Default role which a user gets assigned on registration",
            "value": "Consumer"
        },
        {
            "id": 33,
            "type": 1,
            "name": "system_dispatcher",
            "description": "Optional a HTTP or message queue connection which is used to dispatch events",
            "value": ""
        },
        {
            "id": 32,
            "type": 1,
            "name": "system_mailer",
            "description": "Optional a SMTP connection which is used as mailer",
            "value": ""
        },
        {
            "id": 35,
            "type": 2,
            "name": "user_approval",
            "description": "Whether the user needs to activate the account through an email",
            "value": true
        },
        {
            "id": 34,
            "type": 3,
            "name": "user_pw_length",
            "description": "Minimal required password length",
            "value": 8
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $actual);
        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/backend/config', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/backend/config', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/backend/config', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }
}
