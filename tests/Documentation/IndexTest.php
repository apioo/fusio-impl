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

namespace Fusio\Impl\Tests\Documentation;

use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * IndexTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class IndexTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testGet()
    {
        $response = $this->sendRequest('/doc', 'GET', array(
            'User-Agent' => 'Fusio TestCase',
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "routings": [
        {
            "path": "\/backend\/action",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/action\/list",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/action\/form",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/action\/execute\/$action_id<[0-9]+>",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/action\/$action_id<[0-9]+>",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/app\/token",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/app\/token\/$token_id<[0-9]+>",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/app",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/app\/$app_id<[0-9]+>",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/app\/$app_id<[0-9]+>\/token\/:token_id",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/audit",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/audit\/$audit_id<[0-9]+>",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/config",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/config\/$config_id<[0-9]+>",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/connection",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/connection\/list",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/connection\/form",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/connection\/$connection_id<[0-9]+>",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/cronjob",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/cronjob\/$cronjob_id<[0-9]+>",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/log\/error",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/log\/error\/$error_id<[0-9]+>",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/log",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/log\/$log_id<[0-9]+>",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/rate",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/rate\/$rate_id<[0-9]+>",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/routes",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/routes\/$route_id<[0-9]+>",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/schema",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/schema\/preview\/$schema_id<[0-9]+>",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/schema\/$schema_id<[0-9]+>",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/scope",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/scope\/$scope_id<[0-9]+>",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/user",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/user\/$user_id<[0-9]+>",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/dashboard",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/statistic\/incoming_requests",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/statistic\/most_used_routes",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/statistic\/most_used_apps",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/statistic\/errors_per_route",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/statistic\/issued_tokens",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/statistic\/count_requests",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/statistic\/time_average",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/statistic\/time_per_route",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/account\/change_password",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/import\/process",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/import\/:format",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/backend\/token",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/consumer\/app\/developer",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/consumer\/app\/developer\/$app_id<[0-9]+>",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/consumer\/app\/grant",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/consumer\/app\/grant\/$grant_id<[0-9]+>",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/consumer\/app\/meta",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/consumer\/scope",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/consumer\/token",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/consumer\/authorize",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/consumer\/login",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/consumer\/register",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/consumer\/provider\/:provider",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/consumer\/activate",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/consumer\/account",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/consumer\/account\/change_password",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/authorization\/token",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/foo",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        },
        {
            "path": "\/inspect\/:foo",
            "methods": [
                "GET",
                "POST",
                "PUT",
                "DELETE",
                "PATCH"
            ],
            "version": "*"
        }
    ],
    "links": [
        {
            "rel": "self",
            "href": "http:\/\/127.0.0.1\/doc"
        },
        {
            "rel": "detail",
            "href": "http:\/\/127.0.0.1\/doc\/{version}\/{path}"
        },
        {
            "rel": "api",
            "href": "http:\/\/127.0.0.1\/"
        }
    ]
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }
}
