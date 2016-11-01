<?php
/**
 * Copyright 2016 Google Inc. All Rights Reserved.
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

namespace Google\Cloud\Samples\Translate;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit Tests for transcribe commands.
 */
class TranscribeCommandTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->apiKey = getenv('GOOGLE_API_KEY');
        if (!$this->apiKey) {
            $this->markTestSkipped('No api key was found.');
        }
    }

    public function testTranslate()
    {
        $application = new Application();
        $application->add(new TranslateCommand($this->apiKey));
        $commandTester = new CommandTester($application->get('translate'));
        $commandTester->execute(
            [
                'text' => 'Hello.',
                '-t' => 'ja'
            ],
            ['interactive' => false]
        );
        $this->assertEquals(0, $commandTester->getStatusCode());
        $display = $commandTester->getDisplay();
        $this->assertContains("Source language: en", $display);
        $this->assertContains("Translation:", $display);
    }

    public function testTranslateBadLanguage()
    {
        $application = new Application();
        $application->add(new TranslateCommand($this->apiKey));
        $commandTester = new CommandTester($application->get('translate'));
        $this->setExpectedException('Google\Cloud\Exception\BadRequestException');
        $commandTester->execute(
            [
                'text' => 'Hello.',
                '-t' => 'jp'
            ],
            ['interactive' => false]
        );
    }

    public function testDetectLanguage()
    {
        $application = new Application();
        $application->add(new DetectLanguageCommand($this->apiKey));
        $commandTester = new CommandTester($application->get('detect'));
        $commandTester->execute(
            [
                'text' => 'Hello.',
            ],
            ['interactive' => false]
        );
        $this->assertEquals(0, $commandTester->getStatusCode());
        $display = $commandTester->getDisplay();
        $this->assertContains("Language code: en", $display);
        $this->assertContains("Confidence:", $display);
    }

    public function testListCodes()
    {
        $application = new Application();
        $application->add(new ListCodesCommand($this->apiKey));
        $commandTester = new CommandTester($application->get('list-codes'));
        $commandTester->execute([], ['interactive' => false]);
        $this->assertEquals(0, $commandTester->getStatusCode());
        $display = $commandTester->getDisplay();
        $this->assertContains("\nen\n", $display);
        $this->assertContains("\nja\n", $display);
    }

    public function testListLanguagesInEnglish()
    {
        $application = new Application();
        $application->add(new ListLanguagesCommand($this->apiKey));
        $commandTester = new CommandTester($application->get('list-langs'));
        $commandTester->execute(['-t' => 'en'], ['interactive' => false]);
        $this->assertEquals(0, $commandTester->getStatusCode());
        $display = $commandTester->getDisplay();
        $this->assertContains("ja: Japanese", $display);
    }

    public function testListLanguagesInJapanese()
    {
        $application = new Application();
        $application->add(new ListLanguagesCommand($this->apiKey));
        $commandTester = new CommandTester($application->get('list-langs'));
        $commandTester->execute(['-t' => 'ja'], ['interactive' => false]);
        $this->assertEquals(0, $commandTester->getStatusCode());
        $display = $commandTester->getDisplay();
        $this->assertContains("en: 英語", $display);
    }
}
