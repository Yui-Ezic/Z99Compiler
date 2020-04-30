<?php

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Z99Compiler\ParsingCommand;
use Z99Compiler\SemanticCommand;
use Z99Compiler\TokenizeCommand;

$application = new Application();

$application->add(new TokenizeCommand());
$application->add(new ParsingCommand());
$application->add(new SemanticCommand());

$application->run();