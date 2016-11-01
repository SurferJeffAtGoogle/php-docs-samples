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

use Google\Cloud\Translate\TranslateClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command line utility to detect which language some text is written in.
 */
class DetectLanguageCommand extends Command
{
    function __construct($apiKey)
    {
        parent::__construct();
        $this->apiKey = $apiKey;
    }
    protected function configure()
    {
        $this
            ->setName('detect')
            ->setDescription('Detect which language text was written in using '
                + 'Google Cloud Translate API')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command detects which language text was written in using the Google Cloud Translate API.

    <info>php %command.full_name% "Your text here"</info>

EOF
            )
            ->addArgument(
                'text',
                InputArgument::REQUIRED,
                'The text to examine.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectLanguage($input->getArgument('text'), $output);
    }

    // [START translate_detect_language]
    protected function detectLanguage($text, OutputInterface $output)
    {
        $translate = new TranslateClient([
            'key' => $this->apiKey
        ]);
        $result = $translate->detectLanguage($text);
        $output->writeln("Language code: $result[languageCode]");
        $output->writeln("Confidence: $result[confidence]");
    }
    // [END translate_detect_language]
}
