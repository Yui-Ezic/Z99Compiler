<?php

require 'vendor/autoload.php';

use Z99Parser\Parser;
use Z99Parser\Streams\ArrayStream;
use Z99Parser\Exceptions\ParserException;

$tokenStream = ArrayStream::fromJsonFile('tokens.txt');
$parser = new Parser($tokenStream);
try {
    echo json_encode($parser->program(), JSON_PRETTY_PRINT);
} catch (ParserException $exception) {
    $message = "Parsing failed with error '" . $exception->getMessage() . "' in line " . $exception->getToken()->getLine() . PHP_EOL;
    $message .= 'Token: ' . $exception->getToken();
    echo $message;
}

