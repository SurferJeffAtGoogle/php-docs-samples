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

trait TestMemcacheAppTrait
{
    public function testIndex()
    {
        // Access the modules app top page.
        $resp = $this->get('');
        $this->assertEquals('200', $resp->getStatusCode(),
            'top page status code');

        // Make sure it handles a POST request too, which will increment the
        // counter.
        $resp = $this->post('');
        $this->assertEquals('200', $resp->getStatusCode(),
            'top page status code');
    }

    public function testPutAndGet()
    {
        // Use a random key to avoid colliding with simultaneous tests.
        $key = rand(0, 1000);

        // Test the /memcached REST API.
        $this->put("/memcached/test$key", "sour");
        $this->assertEquals("sour", $this->get("/memcached/test$key"));
        $this->put("/memcached/test$key", "sweet");
        $this->assertEquals("sweet", $this->get("/memcached/test$key"));
    }
}