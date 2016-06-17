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
namespace Google\Cloud\Samples\twilio\test;

use Google\Cloud\TestUtils\AppEngineDeploymentTrait;
use Google\Cloud\TestUtils\FileUtil;

class DeployAppEngineFlexTest extends \PHPUnit_Framework_TestCase
{
    use AppEngineDeploymentTrait;

    public function beforeDeploy()
    {
        $tmpDir = FileUtil::cloneDirectoryIntoTmp(__DIR__ . '/..');
        FileUtil::copyDir(__DIR__ . '/../../../flexible/mailjet', $tmpDir);
        self::$gcloudWrapper->setDir($tmpDir);
        chdir($tmpDir);
        $appYaml = Yaml::parse(file_get_contents('app.yaml'));
        $appYaml['env_variables']['TWILIO_ACCOUNT_SID'] =
            getenv('TWILIO_ACCOUNT_SID');
        $appYaml['env_variables']['TWILIO_AUTH_TOKEN'] =
            getenv('TWILIO_AUTH_TOKEN');
        $appYaml['env_variables']['TWILIO_NUMBER'] =
            getenv('TWILIO_FROM_NUMBER') ?
                getenv('TWILIO_FROM_NUMBER') : getenv('TWILIO_NUMBER');
        file_put_contents('app.yaml', Yaml::dump($appYaml));
    }
    
    public function testSendMessage()
    {
        $resp = $this->client->request('POST', '/send', [
            'form_params' => [
                'recipient' => 'fake@example.com',
            ]
        ]);

        $this->assertEquals('200', $resp->getStatusCode(),
            'send message status code');
    }
}
