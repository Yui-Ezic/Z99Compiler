<?php


namespace Z99Interpreter\Traits;


use RuntimeException;
use Z99Compiler\Entity\BinaryOperator;

trait BoolExpressionTrait
{
    private function calculateBool(BinaryOperator $operator, $left, $right): array
    {
        if ($left->getValue() === null) {
            throw new RuntimeException('Unable process undefined variable ' . $left->getName());
        }

        if ($right->getValue() === null) {
            throw new RuntimeException('Unable process undefined variable ' . $right->getName());
        }

        $left = $left->getValue();
        $right = $right->getValue();

        switch ($operator->getOperator()) {
            case '<':
                $value = $left < $right;
                break;
            case '<=':
                $value = $left <= $right;
                break;
            case '==':
                $value = $left == $right;
                break;
            case '>':
                $value = $left > $right;
                break;
            case '>=':
                $value = $left >= $right;
                break;
            case '!=':
                $value = $left != $right;
                break;
            default:
                throw new RuntimeException('Unknown relOp operator ' . $operator->getOperator());
        }

        return [
            'type' => 'bool',
            'value' => $this->boolToString($value)
        ];
    }

    private function boolToString(bool $value): string
    {
        return $value ? 'true' : 'false';
    }
}