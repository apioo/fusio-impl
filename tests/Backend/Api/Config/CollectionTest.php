<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * CollectionTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class CollectionTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testDocumentation()
    {
        $response = $this->sendRequest('/doc/*/backend/config', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "path": "\/backend\/config",
    "version": "*",
    "status": 1,
    "description": "",
    "schema": {
        "$schema": "http:\/\/json-schema.org\/draft-04\/schema#",
        "id": "urn:schema.phpsx.org#",
        "definitions": {
            "GET-query": {
                "type": "object",
                "title": "query",
                "properties": {
                    "startIndex": {
                        "type": "integer"
                    },
                    "count": {
                        "type": "integer"
                    },
                    "search": {
                        "type": "string"
                    }
                }
            },
            "Config": {
                "type": "object",
                "title": "config",
                "properties": {
                    "id": {
                        "type": "integer"
                    },
                    "type": {
                        "type": "integer"
                    },
                    "name": {
                        "type": "string"
                    },
                    "description": {
                        "type": "string"
                    },
                    "value": {
                        "oneOf": [
                            {
                                "type": "string"
                            },
                            {
                                "type": "number"
                            },
                            {
                                "type": "boolean"
                            },
                            {
                                "type": "null"
                            }
                        ]
                    }
                }
            },
            "Collection": {
                "type": "object",
                "title": "collection",
                "properties": {
                    "totalResults": {
                        "type": "integer"
                    },
                    "startIndex": {
                        "type": "integer"
                    },
                    "entry": {
                        "type": "array",
                        "items": {
                            "$ref": "#\/definitions\/Config"
                        }
                    }
                }
            },
            "GET-200-response": {
                "$ref": "#\/definitions\/Collection"
            }
        }
    },
    "methods": {
        "GET": {
            "queryParameters": "#\/definitions\/GET-query",
            "responses": {
                "200": "#\/definitions\/GET-200-response"
            }
        }
    },
    "links": [
        {
            "rel": "openapi",
            "href": "\/export\/openapi\/*\/backend\/config"
        },
        {
            "rel": "swagger",
            "href": "\/export\/swagger\/*\/backend\/config"
        },
        {
            "rel": "raml",
            "href": "\/export\/raml\/*\/backend\/config"
        }
    ]
}
JSON;

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
    "totalResults": 22,
    "startIndex": 0,
    "itemsPerPage": 16,
    "entry": [
        {
            "id": 1,
            "type": 2,
            "name": "app_approval",
            "description": "If true the status of a new app is PENDING so that an administrator has to manually activate the app",
            "value": "0"
        },
        {
            "id": 2,
            "type": 3,
            "name": "app_consumer",
            "description": "The max amount of apps a consumer can register",
            "value": "16"
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
            "value": "8"
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
            "id": 14,
            "type": 6,
            "name": "mail_register_body",
            "description": "Body of the activation mail",
            "value": "Hello {name},\n\nyou have successful registered at Fusio.\nTo activate you account please visit the following link:\nhttp:\/\/127.0.0.1\/projects\/fusio\/public\/consumer\/#activate?token={token}"
        },
        {
            "id": 13,
            "type": 1,
            "name": "mail_register_subject",
            "description": "Subject of the activation mail",
            "value": "Fusio registration"
        },
        {
            "id": 15,
            "type": 1,
            "name": "mail_sender",
            "description": "Email address which is used in the \"From\" header",
            "value": ""
        },
        {
            "id": 16,
            "type": 1,
            "name": "provider_facebook_secret",
            "description": "Facebook app secret",
            "value": ""
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
    "totalResults": 22,
    "startIndex": 0,
    "itemsPerPage": 80,
    "entry": [
        {
            "id": 1,
            "type": 2,
            "name": "app_approval",
            "description": "If true the status of a new app is PENDING so that an administrator has to manually activate the app",
            "value": "0"
        },
        {
            "id": 2,
            "type": 3,
            "name": "app_consumer",
            "description": "The max amount of apps a consumer can register",
            "value": "16"
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
            "value": "8"
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
            "id": 14,
            "type": 6,
            "name": "mail_register_body",
            "description": "Body of the activation mail",
            "value": "Hello {name},\n\nyou have successful registered at Fusio.\nTo activate you account please visit the following link:\nhttp:\/\/127.0.0.1\/projects\/fusio\/public\/consumer\/#activate?token={token}"
        },
        {
            "id": 13,
            "type": 1,
            "name": "mail_register_subject",
            "description": "Subject of the activation mail",
            "value": "Fusio registration"
        },
        {
            "id": 15,
            "type": 1,
            "name": "mail_sender",
            "description": "Email address which is used in the \"From\" header",
            "value": ""
        },
        {
            "id": 16,
            "type": 1,
            "name": "provider_facebook_secret",
            "description": "Facebook app secret",
            "value": ""
        },
        {
            "id": 18,
            "type": 1,
            "name": "provider_github_secret",
            "description": "GitHub app secret",
            "value": ""
        },
        {
            "id": 17,
            "type": 1,
            "name": "provider_google_secret",
            "description": "Google app secret",
            "value": ""
        },
        {
            "id": 19,
            "type": 1,
            "name": "recaptcha_secret",
            "description": "ReCaptcha secret",
            "value": ""
        },
        {
            "id": 20,
            "type": 1,
            "name": "scopes_default",
            "description": "If a user registers through the consumer API the following scopes are assigned",
            "value": "authorization,consumer"
        },
        {
            "id": 22,
            "type": 2,
            "name": "user_approval",
            "description": "Whether the user needs to activate the account through an email",
            "value": "1"
        },
        {
            "id": 21,
            "type": 3,
            "name": "user_pw_length",
            "description": "Minimal required password length",
            "value": "8"
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
