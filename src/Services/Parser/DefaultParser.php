<?php


namespace Z99Compiler\Services\Parser;

use RuntimeException;
use Z99Parser\Parser;
use Z99Parser\Streams\ArrayStream;
use Z99Parser\Exceptions\ParserException;

class DefaultParser
{
    public function parsing($fileName): string
    {
        $tokenStream = ArrayStream::fromJsonFile($fileName);
        $parser = new Parser($tokenStream);
        try {
            return json_encode($parser->program(), JSON_PRETTY_PRINT);
        } catch (ParserException $exception) {
            $message = "Parsing failed with error '" . $exception->getMessage() . "' in line " . $exception->getToken()->getLine() . PHP_EOL;
            $message .= 'Token: ' . $exception->getToken();
            throw new RuntimeException($message);
        }
    }
}