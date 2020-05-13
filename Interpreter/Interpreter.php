<?php


namespace Z99Interpreter;


use SplStack;
use RuntimeException;
use Z99Compiler\Entity\Constant;
use Z99Compiler\Entity\Identifier;
use Z99Compiler\Entity\BinaryOperator;
use Z99Compiler\Entity\JumpIf;
use Z99Compiler\Entity\UnaryOperator;
use Z99Compiler\Tables\ConstantsTable;
use Z99Compiler\Tables\IdentifiersTable;
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
     * @var IdentifiersTable
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
     * @var int
     */
    private $current = 0;

    /**
     * Interpreter constructor.
     * @param array $RPNCode
     * @param ConstantsTable $constants
     * @param IdentifiersTable $identifiers
     */
    public function __construct(array $RPNCode, ConstantsTable $constants, IdentifiersTable $identifiers)
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
        $size = count($this->RPNCode);
        while ($this->current < $size) {
            $item = $this->RPNCode[$this->current];
            if ($item instanceof Constant || $item instanceof Identifier) {
                $this->stack->push($item);
            } elseif ($item instanceof BinaryOperator) {
                $this->binaryOperator($item);
            } elseif ($item instanceof UnaryOperator) {
                $this->unaryOperator($item);
            } elseif ($item instanceof JumpIf) {
                $this->jumpIf($item);
            } else {
                throw new RuntimeException('Unknown item ' . get_class($item));
            }
            $this->current++;
        }
    }

    /**
     * @param BinaryOperator $operator
     */
    private function binaryOperator(BinaryOperator $operator): void
    {
        $right = $this->stakPop();
        $left = $this->stakPop();

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
            if ($leftType !== 'real' && ($leftType !== $right->getType())) {
                throw new RuntimeException('Cannot set variable ' . $left->getName() . ' to ' . $right->getType());
            }

            $this->identifiers->changeValue($left->getId(), $right->getValue());
            return;
        }

        throw new RuntimeException('Unknown binary operator ' . $operator->getType());
    }

    private function jumpIf(JumpIf $jumpIf): void
    {
        $expression = $this->stakPop();
        if ($expression instanceof Constant) {
            $value = $expression->getValue();
            if ($value === 'true') {
                $value = true;
            } elseif ($value === 'false') {
                $value = false;
            } else {
                $value = (bool) $value;
            }

            if (!$value) {
                $this->current = $jumpIf->getAddress() - 1;
            }
            return;
        }

        throw new RuntimeException('Expected constant instead of .' . get_class($expression));
    }

    /**
     * @return IdentifiersTable
     */
    public function getIdentifiers(): IdentifiersTable
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

    private function unaryOperator(UnaryOperator $operator): void
    {
        $operand = $this->stakPop();

        if ($operator->isPlus()) {
            $this->stack->push($operand);
            return;
        }

        if ($operator->isMinus()) {
            $value =  - $operand->getValue();
            $constant = $this->constants->addConstant($value, $operand->getType());
            $this->stack->push($constant);
            return;
        }

        if ($operator->isOutput()) {
            echo $operand->getValue() . ' ';
            return;
        }

        if ($operator->isInput()) {
            $value = readline();
            $operand->setValue($value);
            return;
        }

        throw new RuntimeException('Unknown unary operator ' . $operator->getType());
    }

    private function stakPop() {
        $item = $this->stack->pop();

        if ($item instanceof Identifier) {
            return $this->identifiers->findByName($item->getName());
        }

        return $item;
    }
}