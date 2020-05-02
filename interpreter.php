<?php

use SemanticAnalyzer\BinaryOperator;
use SemanticAnalyzer\Constant;
use SemanticAnalyzer\Identifier;
use Z99Interpreter\Interpreter;

require 'vendor/autoload.php';

function toObject($item)
{
    if ($item['object'] === 'Identifier') {
        return new Identifier($item['id'], $item['name'], $item['type'], $item['value']);
    }

    if ($item['object'] === 'Constant') {
        return new Constant($item['id'], $item['value'], $item['type']);
    }

    if ($item['object'] === 'BinaryOperator') {
        return new BinaryOperator($item['operator'], $item['type']);
    }

    throw new RuntimeException('Unknown object type. ' . var_export($item, true));
}

function turnArrayItemsToObject(array $array)
{
    foreach ($array as $key => $value) {
        $array[$key] = toObject($value);
    }

    return $array;
}

$semanticResult = file_get_contents('semantic.json');
$semanticResult = json_decode($semanticResult, true, 512, JSON_THROW_ON_ERROR);

$RPNCode = $semanticResult['RPNCode'];
foreach ($RPNCode as $key => $instruction) {
    $RPNCode[$key] = turnArrayItemsToObject($instruction);
}

$identifiers = turnArrayItemsToObject($semanticResult['Identifiers']);
$constants = turnArrayItemsToObject($semanticResult['Constants']);

$interpreter = new Interpreter($RPNCode, $constants, $identifiers);
$interpreter->process();

//echo 'Identifiers:' . PHP_EOL;
//echo 'Id   Name       Type       Value' . PHP_EOL;
//foreach ($interpreter->getIdentifiers() as $identifier) {
//    echo $identifier . PHP_EOL;
//}
//echo PHP_EOL;

//$output->writeln('<comment>Constants:</comment>');
//echo 'Id   Value      Type' . PHP_EOL;
//foreach ($semantic->getConstants() as $constant) {
//    $output->writeln($constant);
//}
//$output->writeln('');