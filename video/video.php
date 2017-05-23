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

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

$application = new Application();

$application->add(new Command('face'))
    ->setName('face')
    ->setDescription('Detect faces in video '
        . 'Google Cloud Video Intelligence API')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command finds faces in an image using
the Google Cloud Vision API.

    <info>php %command.full_name% -k YOUR-API-KEY path/to/image.png</info>

EOF
    )
    ->addArgument(
        'uri',
        InputArgument::REQUIRED,
        'Uri pointing to a video.'
    )
    ->setCode(function (InputInterface $input, OutputInterface $output) {
        $uri = $input->getArgument('uri');
        $result = require __DIR__ . '/src/detect_face.php';
    });

// for testing
if (getenv('PHPUNIT_TESTS') === '1') {
    return $application;
}
$application->run();
