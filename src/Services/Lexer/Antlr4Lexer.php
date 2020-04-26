<?php


namespace Z99Compiler\Services\Lexer;


use Antlr\Antlr4\Runtime\CommonToken;
use Antlr\Antlr4\Runtime\Utils\StringUtils;
use Generated\Z99Lexer;
use Antlr\Antlr4\Runtime\InputStream;
use Antlr\Antlr4\Runtime\CommonTokenStream;
use Antlr\Antlr4\Runtime\Error\Listeners\ConsoleErrorListener;

class Antlr4Lexer
{
    public function tokenize($fileName): string
    {
        $input = InputStream::fromPath($fileName);

        $lexer = new Z99Lexer($input);
        $lexer->addErrorListener(new ConsoleErrorListener());
        $tokens = new CommonTokenStream($lexer);
        $tokens->fill();

        $tokenMap = $lexer->getTokenTypeMap();
        $tokenMap = array_flip($tokenMap);
        $array = [];
        foreach ($tokens->getAllTokens() as $token) {
            $array[] = $this->tokenToArray($token, $tokenMap);
        }

        return json_encode($array);
    }

    private function tokenToArray(CommonToken $token, array $tokenMap): array
    {
        $tokenType = array_key_exists($token->getType(), $tokenMap) ? $tokenMap[$token->getType()] : 'EOF';
        return [
            'line' => $token->getLine(),
            'type' => $tokenType,
            'string' => StringUtils::escapeWhitespace($token->getText() ?? ''),
            'index' => null
        ];
    }
}