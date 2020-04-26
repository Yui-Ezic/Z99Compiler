<?php

namespace Z99Parser;

require 'vendor/autoload.php';

use Antlr\Antlr4\Runtime\CommonToken;
use Antlr\Antlr4\Runtime\CommonTokenStream;
use Antlr\Antlr4\Runtime\Error\Listeners\ConsoleErrorListener;
use Antlr\Antlr4\Runtime\InputStream;
use Antlr\Antlr4\Runtime\Utils\StringUtils;
use Generated\Z99Lexer;

function token_to_array(CommonToken $token, array $tokenMap)
{
    $tokenType = array_key_exists($token->getType(), $tokenMap) ? $tokenMap[$token->getType()] : 'EOF';
    return [
        'line' => $token->getLine(),
        'type' => $tokenType,
        'string' => StringUtils::escapeWhitespace($token->getText() ?? ''),
        'index' => null
    ];
}

$input = InputStream::fromPath('example\first.z99');

$lexer = new Z99Lexer($input);
$lexer->addErrorListener(new ConsoleErrorListener());
$tokens = new CommonTokenStream($lexer);
$tokens->fill();

$tokenMap = $lexer->getTokenTypeMap();
$tokenMap = array_flip($tokenMap);
$file = fopen('tokens.txt', 'wb');
$array = [];
foreach ($tokens->getAllTokens() as $token) {
    $array[] = token_to_array($token, $tokenMap);
}
fwrite($file, json_encode($array));


