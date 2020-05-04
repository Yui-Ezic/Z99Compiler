<?php


namespace Z99Compiler\Services\Parser;

use RuntimeException;
use Z99Parser\Parser;
use Z99Compiler\Entity\Tree\Node;
use Z99Parser\Streams\ArrayStream;
use Z99Parser\Exceptions\ParserException;
use Z99Parser\Streams\TokenStreamInterface;

class DefaultParser
{
    public function parsingFile($fileName): Node
    {
        $tokenStream = ArrayStream::fromJsonFile($fileName);
        return $this->parsing($tokenStream);
    }

    public function parsingTokenArray(array $tokens): Node
    {
        $tokenStream = new ArrayStream($tokens);
        return $this->parsing($tokenStream);
    }

    public function parsing(TokenStreamInterface $tokenStream): Node
    {
        $parser = new Parser($tokenStream);
        try {
            return $parser->program();
        } catch (ParserException $exception) {
            $message = "Parsing failed with error '" . $exception->getMessage() . "' in line " . $exception->getToken()->getLine() . PHP_EOL;
            $message .= 'Token: ' . $exception->getToken();
            throw new RuntimeException($message, 0, $exception);
        }
    }
}