<?php


namespace Z99Interpreter\Traits;


use RuntimeException;
use Z99Compiler\Entity\BinaryOperator;

trait ArithmeticTrait
{
    /**
     * @param BinaryOperator $operator
     * @param $left
     * @param $right
     * @return array
     */
    private function calculate(BinaryOperator $operator, $left, $right): array
    {
        if ($left->getValue() === null) {
            throw new RuntimeException('Unable process undefined variable ' . $left->getName());
        }

        if ($right->getValue() === null) {
            throw new RuntimeException('Unable process undefined variable ' . $right->getName());
        }

        $allowedTypes = ['int', 'real'];

        if (!in_array($left->getType(), $allowedTypes, true)) {
            throw new RuntimeException('Unsupported type ' . $left->getType() . ' in arithmetic operation');
        }

        if (!in_array($right->getType(), $allowedTypes, true)) {
            throw new RuntimeException('Unsupported type ' . $right->getType() . ' in arithmetic operation');
        }

        if ($operator->getType() === 'Plus') {
            return $this->plus($left, $right);
        }

        if ($operator->getType() === 'Minus') {
            return $this->minus($left, $right);
        }

        if ($operator->getType() === 'Star') {
            return $this->star($left, $right);
        }

        if ($operator->getType() === 'Slash') {
            return $this->slash($left, $right);
        }

        if ($operator->getType() === 'Caret') {
            return $this->caret($left, $right);
        }

        throw new RuntimeException('Unknown arithmetic operator ' . $operator->getType());
    }

    /**
     * @param $left
     * @param $right
     * @return array
     */
    private function plus($left, $right): array
    {
        $value = $left->getValue() + $right->getValue();
        if ($left->getType() === 'real' || $right->getType() === 'real') {
            $type = 'real';
        } else {
            $type = 'int';
        }

        return [
            'value' => $value,
            'type' => $type
        ];
    }

    /**
     * @param $left
     * @param $right
     * @return array
     */
    private function minus($left, $right): array
    {
        $value = $left->getValue() - $right->getValue();
        if ($left->getType() === 'real' || $right->getType() === 'real') {
            $type = 'real';
        } else {
            $type = 'int';
        }

        return [
            'value' => $value,
            'type' => $type
        ];
    }

    /**
     * @param $left
     * @param $right
     * @return array
     */
    private function star($left, $right): array
    {
        $value = $left->getValue() * $right->getValue();
        $type = 'real';

        return [
            'value' => $value,
            'type' => $type
        ];
    }

    /**
     * @param $left
     * @param $right
     * @return array
     */
    private function slash($left, $right): array
    {
        if ($right->getValue() == 0) {
            throw new RuntimeException('Division by zero.');
        }
        $value = $left->getValue() / $right->getValue();
        $type = 'real';

        return [
            'value' => $value,
            'type' => $type
        ];
    }

    /**
     * @param $left
     * @param $right
     * @return array
     */
    private function caret($left, $right): array
    {
        $value = $left->getValue() ** $right->getValue();
        $type = 'real';

        return [
            'value' => $value,
            'type' => $type
        ];
    }
}