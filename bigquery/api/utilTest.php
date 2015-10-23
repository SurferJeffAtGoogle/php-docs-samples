<?php
/**
 * Copyright 2015 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
require 'util.php';

class UtilTest extends PHPUnit_Framework_TestCase
{
    protected static $hasCredentials;
    protected static $bigquery;
    protected static $projectId;
    protected static $shakespeareQuery;

    public static function setUpBeforeClass()
    {
        $path = getenv('GOOGLE_APPLICATION_CREDENTIALS');
        self::$hasCredentials = $path && file_exists($path) &&
            filesize($path) > 0;
        if (self::$hasCredentials) {
            self::$bigquery = createAuthorizedClient();
            self::$projectId = getenv('GOOGLE_CLOUD_PROJECT');
            self::$shakespeareQuery =
                'SELECT TOP(corpus, 10) as title, COUNT(*) as unique_words '.
                'FROM [publicdata:samples.shakespeare]';
        }
    }

    function setUp()
    {
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }
    }

    function isShakespeare($rows)
    {
        $foundKingLear = false;
        $foundHamlet = false;
        foreach ($rows as $row) {
            foreach ($row['f'] as $field) {
                $foundHamlet = $foundHamlet || $field['v'] == 'hamlet';
                $foundKingLear = $foundKingLear || $field['v'] == 'kinglear';
            }
        }
        return $foundHamlet && $foundKingLear;
    }

    public function testSyncQuery()
    {
        $rows = SyncQuery(
            self::$bigquery,
            self::$projectId,
            self::$shakespeareQuery
        );
        $this->assertTrue($this->isShakespeare($rows));
    }
}
