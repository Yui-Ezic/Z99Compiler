<?php


namespace Z99Compiler\Services\Lexer;

use Z99Compiler\Entity\Token;
use Z99Lexer\Lexer;
use Z99Lexer\FSM\FSM;
use RuntimeException;
use Z99Lexer\Stream\FileStream;
use Z99Lexer\Exceptions\LexerException;

class DefaultLexer
{
    /**
     * @var FSM
     */
    private $grammar;

    public function __construct(FSM $grammar)
    {
        $this->grammar = $grammar;
    }

    /**
     * @param string $fileName
     * @return Token[]
     */
    public function tokenize(string $fileName) : array
    {
        $stream = new FileStream($fileName);
        $lexer = new Lexer($stream, $this->grammar);

        try {
            $lexer->tokenize();
            return $lexer->getTokens();
        } catch (LexerException $e) {
            $message = "Lexer failed with error '" . $e->getMessage() . "' in line " . $e->getErrorLine() . PHP_EOL;
            $message .= 'String: ' . $e->getString();
            throw new RuntimeException($message);
        }
    }
}