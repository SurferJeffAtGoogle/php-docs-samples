<?php

require __DIR__ . '/vendor/autoload.php';

use Google\Cloud\Samples\Language\AnalyzeEntitiesCommand;
use Google\Cloud\Samples\Language\AnalyzeSentimentCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new AnalyzeEntitiesCommand());
$application->add(new AnalyzeSentimentCommand());
$application->run();
