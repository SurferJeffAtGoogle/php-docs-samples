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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command line utility to translate.
 */
class ListCodesCommand extends Command
{
    public function __construct($apiKey)
    {
        parent::__construct();
        $this->apiKey = $apiKey;
    }
    protected function configure()
    {
        $this
            ->setName('list-codes')
            ->setDescription('List all the language codes in the '.
                'Google Cloud Translate API')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command lists all the language codes in the Google Cloud Translate API.

    <info>php %command.full_name%</info>

EOF
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->listCodes($output);
    }

    // [START translate_list_codes]
    protected function listCodes(OutputInterface $output)
    {
        $translate = new TranslateClient([
            'key' => $this->apiKey,
        ]);
        foreach ($translate->languages() as $code) {
            $output->writeln($code);
        }
    }
    // [END translate_list_codes]
}
