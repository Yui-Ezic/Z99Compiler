<?php


namespace Z99Interpreter;


use SplStack;
use RuntimeException;
use Z99Compiler\Entity\Constant;
use Z99Compiler\Entity\Identifier;
use Z99Compiler\Entity\BinaryOperator;
use Z99Compiler\Tables\ConstantsTable;
use Z99Compiler\Tables\IdentifierTable;
use Z99Interpreter\Traits\ArithmeticTrait;
use Z99Interpreter\Traits\BoolExpressionTrait;

class Interpreter
{
    use ArithmeticTrait, BoolExpressionTrait;

    /**
     * @var SplStack
     */
    private $stack;

    /**
     * @var IdentifierTable
     */
    private $identifiers;

    /**
     * @var Constant[]
     */
    private $constants;

    /**
     * @var array
     */
    private $RPNCode;

    /**
     * Interpreter constructor.
     * @param array $RPNCode
     * @param ConstantsTable $constants
     * @param IdentifierTable $identifiers
     */
    public function __construct(array $RPNCode, ConstantsTable $constants, IdentifierTable $identifiers)
    {
        $this->RPNCode = $RPNCode;
        $this->constants = $constants;
        $this->identifiers = $identifiers;
        $this->stack = new SplStack();
    }

    /**
     * Interpret program
     */
    public function process(): void
    {
        foreach ($this->RPNCode as $instruction) {
            $this->instruction($instruction);
        }
    }

    /**
     * @param array $instruction
     */
    private function instruction(array $instruction): void
    {
        foreach ($instruction as $item) {
            if ($item instanceof Constant || $item instanceof Identifier) {
                $this->stack->push($item);
            } elseif ($item instanceof BinaryOperator) {
                $this->binaryOperator($item);
            }
        }
    }

    /**
     * @param BinaryOperator $operator
     */
    private function binaryOperator(BinaryOperator $operator): void
    {
        $left = $this->stack->pop();
        $right = $this->stack->pop();

        if ($operator->isAddOp() || $operator->isMultOp()) {
            $result = $this->calculate($operator, $left, $right);
            $constant = $this->constants->addConstant($result['value'], $result['type']);
            $this->stack->push($constant);
            return;
        }

        if ($operator->isRelOp()) {
            $result = $this->calculateBool($operator, $left, $right);
            $constant = $this->constants->addConstant($result['value'], $result['type']);
            $this->stack->push($constant);
            return;
        }

        if ($operator->isAssignOp()) {
            /** @var $left Identifier */
            $leftType = $left->getType();
            if ($leftType !== 'real' && $leftType !== 'int' && ($leftType !== $right->getType())) {
                throw new RuntimeException('Cannot set variable ' . $left->getName() . ' to ' . $right->getType());
            }

            $this->identifiers->changeValue($left->getId(), $right->getValue());
            return;
        }

        throw new RuntimeException('Unknown binary operator ' . $operator->getType());
    }

    /**
     * @return IdentifierTable
     */
    public function getIdentifiers(): IdentifierTable
    {
        return $this->identifiers;
    }

    /**
     * @return ConstantsTable
     */
    public function getConstants(): ConstantsTable
    {
        return $this->constants;
    }
}