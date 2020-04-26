<?php


namespace Z99Compiler\Services\Lexer;

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

    public function tokenize(string $fileName) : string
    {
        $stream = new FileStream($fileName);
        $lexer = new Lexer($stream, $this->grammar);

        try {
            $lexer->tokenize();
            return json_encode($lexer->getTokens(), JSON_PRETTY_PRINT);
        } catch (LexerException $e) {
            $message = "Lexer failed with error '" . $e->getMessage() . "' in line " . $e->getErrorLine() . PHP_EOL;
            $message .= 'String: ' . $e->getString();
            throw new RuntimeException($message);
        }
    }
}