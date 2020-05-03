<?php

require __DIR__.'/vendor/autoload.php';

use Commands\TokenizeCommand;
use Commands\ParsingCommand;
use Commands\SemanticCommand;
use Symfony\Component\Console\Application;


$application = new Application();

$application->add(new TokenizeCommand());
$application->add(new ParsingCommand());
$application->add(new SemanticCommand());

$application->run();