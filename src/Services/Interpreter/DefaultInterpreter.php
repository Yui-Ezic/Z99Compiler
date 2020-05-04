<?php


namespace Z99Compiler\Services\Interpreter;


use RuntimeException;
use Z99Compiler\Entity\BinaryOperator;
use Z99Compiler\Entity\Constant;
use Z99Compiler\Entity\Identifier;
use Z99Compiler\Tables\ConstantsTable;
use Z99Compiler\Tables\IdentifierTable;
use Z99Interpreter\Interpreter;

class DefaultInterpreter
{
    public function processFile($fileName): array
    {
        $semanticResult = file_get_contents($fileName);
        $semanticResult = json_decode($semanticResult, true, 512, JSON_THROW_ON_ERROR);

        $RPNCode = $semanticResult['RPNCode'];
        foreach ($RPNCode as $key => $instruction) {
            $RPNCode[$key] = $this->turnArrayItemsToObjects($instruction);
        }

        $identifiers = IdentifierTable::fromArray($semanticResult['Identifiers']);
        $constants = ConstantsTable::fromArray($semanticResult['Constants']);

        return $this->process($RPNCode, $constants, $identifiers);
    }

    /**
     * @param $RPNCode
     * @param $constants
     * @param $identifiers
     * @return array
     */
    public function process($RPNCode, $constants, $identifiers): array
    {
        $interpreter = new Interpreter($RPNCode, $constants, $identifiers);
        $interpreter->process();

        return [
            'Constants' => $interpreter->getConstants(),
            'Identifiers' => $interpreter->getIdentifiers()
        ];
    }

    private function itemToObject($item)
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

    private function turnArrayItemsToObjects(array $array): array
    {
        foreach ($array as $key => $value) {
            $array[$key] = $this->itemToObject($value);
        }

        return $array;
    }
}