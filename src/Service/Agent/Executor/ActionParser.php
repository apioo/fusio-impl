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

namespace Fusio\Impl\Service\Agent\Executor;

/**
 * Extracts the action name and PHP code from the text response of the action agent. The parser is tolerant to common
 * LLM formatting i.e. markdown code fences, markdown around the name or CRLF line endings
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
final class ActionParser
{
    public static function extractName(string $content): ?string
    {
        if (preg_match('/^[\s#>*_`]*Action\s*:[\s*_`]*([A-Za-z0-9_-]+)/im', $content, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public static function extractCode(string $content): ?string
    {
        $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $content));

        $start = null;
        foreach ($lines as $index => $line) {
            if (str_starts_with(trim($line), '<?php')) {
                $start = $index;
                break;
            }
        }

        if ($start === null) {
            return null;
        }

        // the end of the code is the last closing line of the closure, we search from the bottom since the code itself
        // can contain other closures which also end with "};"
        $end = null;
        for ($i = count($lines) - 1; $i > $start; $i--) {
            if (trim($lines[$i]) === '};') {
                $end = $i;
                break;
            }
        }

        if ($end !== null) {
            $code = array_slice($lines, $start, $end - $start + 1);
        } else {
            // fallback in case the closing line is missing, take everything except a trailing markdown fence
            $code = array_values(array_filter(array_slice($lines, $start), fn(string $line) => !str_starts_with(trim($line), '```')));
        }

        // normalize the opening tag i.e. "<?php " with trailing whitespace
        $code[0] = trim($code[0]);

        return rtrim(implode("\n", $code)) . "\n";
    }
}
