<?php

require 'vendor/autoload.php';

use Z99Lexer\Lexer;
use Z99lexer\FSM\FSM;
use Z99Lexer\Stream\FileStream;
use Z99Lexer\Exceptions\LexerException;

/**
 * @var $fsm FSM
 */
$fsm = require 'grammar.php';

$stream = new FileStream('example\first.z99');
$lexer = new Lexer($stream, $fsm);

try {
    $lexer->tokenize();

    $file = fopen('output.txt', 'wb');
    fwrite($file, json_encode($lexer->getTokens(), JSON_PRETTY_PRINT));
} catch (LexerException $e) {
    $message = "Lexer failed with error '" . $e->getMessage() . "' in line " . $e->getLine() . PHP_EOL;
    $message .= 'String: ' . $e->getString();
    echo $message;
}
