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
    "totalResults": 14,
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
            "id": 14,
            "type": 1,
            "name": "authorization_url",
            "description": "Url where the user can authorize for the OAuth2 flow",
            "value": ""
        },
        {
            "id": 11,
            "type": 1,
            "name": "cors_allow_origin",
            "description": "If set each API response contains a Access-Control-Allow-Origin header with the provided value",
            "value": ""
        },
        {
            "id": 5,
            "type": 6,
            "name": "mail_register_body",
            "description": "Body of the activation mail",
            "value": "Hello {name},\n\nyou have successful registered at Fusio.\nTo activate you account please visit the following link:\nhttp:\/\/127.0.0.1\/projects\/fusio\/public\/consumer\/#activate?token={token}"
        },
        {
            "id": 4,
            "type": 1,
            "name": "mail_register_subject",
            "description": "Subject of the activation mail",
            "value": "Fusio registration"
        },
        {
            "id": 6,
            "type": 1,
            "name": "mail_sender",
            "description": "Email address which is used in the \"From\" header",
            "value": ""
        },
        {
            "id": 7,
            "type": 1,
            "name": "provider_facebook_secret",
            "description": "Facebook app secret",
            "value": ""
        },
        {
            "id": 9,
            "type": 1,
            "name": "provider_github_secret",
            "description": "GitHub app secret",
            "value": ""
        },
        {
            "id": 8,
            "type": 1,
            "name": "provider_google_secret",
            "description": "Google app secret",
            "value": ""
        },
        {
            "id": 10,
            "type": 1,
            "name": "recaptcha_secret",
            "description": "ReCaptcha secret",
            "value": ""
        },
        {
            "id": 3,
            "type": 1,
            "name": "scopes_default",
            "description": "If a user registers through the consumer API the following scopes are assigned",
            "value": "authorization,consumer"
        },
        {
            "id": 13,
            "type": 2,
            "name": "user_approval",
            "description": "Whether the user needs to activate the account through an email",
            "value": "1"
        },
        {
            "id": 12,
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


    public function testGetCount()
    {
        $response = $this->sendRequest('/backend/config?count=80', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "totalResults": 14,
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
            "id": 14,
            "type": 1,
            "name": "authorization_url",
            "description": "Url where the user can authorize for the OAuth2 flow",
            "value": ""
        },
        {
            "id": 11,
            "type": 1,
            "name": "cors_allow_origin",
            "description": "If set each API response contains a Access-Control-Allow-Origin header with the provided value",
            "value": ""
        },
        {
            "id": 5,
            "type": 6,
            "name": "mail_register_body",
            "description": "Body of the activation mail",
            "value": "Hello {name},\n\nyou have successful registered at Fusio.\nTo activate you account please visit the following link:\nhttp:\/\/127.0.0.1\/projects\/fusio\/public\/consumer\/#activate?token={token}"
        },
        {
            "id": 4,
            "type": 1,
            "name": "mail_register_subject",
            "description": "Subject of the activation mail",
            "value": "Fusio registration"
        },
        {
            "id": 6,
            "type": 1,
            "name": "mail_sender",
            "description": "Email address which is used in the \"From\" header",
            "value": ""
        },
        {
            "id": 7,
            "type": 1,
            "name": "provider_facebook_secret",
            "description": "Facebook app secret",
            "value": ""
        },
        {
            "id": 9,
            "type": 1,
            "name": "provider_github_secret",
            "description": "GitHub app secret",
            "value": ""
        },
        {
            "id": 8,
            "type": 1,
            "name": "provider_google_secret",
            "description": "Google app secret",
            "value": ""
        },
        {
            "id": 10,
            "type": 1,
            "name": "recaptcha_secret",
            "description": "ReCaptcha secret",
            "value": ""
        },
        {
            "id": 3,
            "type": 1,
            "name": "scopes_default",
            "description": "If a user registers through the consumer API the following scopes are assigned",
            "value": "authorization,consumer"
        },
        {
            "id": 13,
            "type": 2,
            "name": "user_approval",
            "description": "Whether the user needs to activate the account through an email",
            "value": "1"
        },
        {
            "id": 12,
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
