<?php

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;

$apiKey = 'YOUR-API-KEY';

$application = new Application();
$application->add(new Google\Cloud\Samples\Translate\TranslateCommand($apiKey));
$application->run();
