<?php


namespace Z99Lexer\LexerInterfaces;


use Z99Compiler\Entity\Constant;
use Z99Compiler\Entity\Identifier;
use Z99Compiler\Entity\Token;

interface LexerReaderInterface
{
    /**
     * Returns Constants table
     *
     * @return Constant[]
     */
    public function getConstants(): array;

    /**
     * Returns Tokens table
     *
     * @return Token[]
     */
    public function getTokens(): array;

    /**
     * Returns Identifiers table
     *
     * @return Identifier[]
     */
    public function getIdentifiers(): array;
}