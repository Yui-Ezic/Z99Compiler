<?php

require __DIR__.'/vendor/autoload.php';

use Commands\RunCommand;
use Commands\TokenizeCommand;
use Commands\ParsingCommand;
use Commands\SemanticCommand;
use Commands\InterpreterCommand;
use Symfony\Component\Console\Application;
use Z99Compiler\Services\Interpreter\DefaultInterpreter;
use Z99Compiler\Services\Lexer\DefaultLexer;
use Z99Compiler\Services\Parser\DefaultParser;
use Z99Compiler\Services\SemanticAnalyzer\DefaultSemanticAnalyzer;


$application = new Application();

$application->add(new TokenizeCommand());
$application->add(new ParsingCommand());
$application->add(new SemanticCommand());
$application->add(new InterpreterCommand(new DefaultInterpreter()));
$application->add(new RunCommand(
    new DefaultLexer(require 'grammar.php'),
    new DefaultParser(),
    new DefaultSemanticAnalyzer(),
    new DefaultInterpreter()
));

$application->run();