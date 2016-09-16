<?php

require __DIR__ . '/vendor/autoload.php';

use Google\Cloud\Samples\Language\AnalyzeEntitiesCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new AnalyzeEntitiesCommand());
$application->run();
