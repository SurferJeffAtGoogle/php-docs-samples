<?php
/**
 * Copyright 2016 Google Inc.
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
namespace Google\Cloud\Test\Memcache;

use Google\Cloud\TestUtils\AppEngineDeploymentTrait;

class DeployTest extends \PHPUnit_Framework_TestCase
{
    use AppEngineDeploymentTrait;
    use TestMemcacheAppTrait;

    /**
     * HTTP PUTs the body to the url path.
     * @param $path string
     * @param $body string
     */
    private function put($path, $body)
    {
        $url = join('/', [trim(self::$gcloudWrapper->getBaseUrl(), '/'),
            trim($path, '/')]);
        $request = new \GuzzleHttp\Psr7\Request('PUT', $url, array(), $body);
        $this->client->send($request);
    }

    /**
     * HTTP GETs the url path.
     * @param $path string
     * @return string The HTTP Response.
     */
    private function get($path)
    {
        return $this->client->get($path)->getBody()->getContents();
    }

    /**
     * HTTP GETs the url path.
     * @param $path string
     * @return string The HTTP Response.
     */
    private function post($path)
    {
        return $this->client->post($path)->getBody()->getContents();
    }
}
