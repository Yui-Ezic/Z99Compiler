<?php


namespace Z99Interpreter;


use SplStack;
use RuntimeException;
use SemanticAnalyzer\Constant;
use SemanticAnalyzer\Identifier;
use SemanticAnalyzer\BinaryOperator;

class Interpreter
{
    /**
     * @var SplStack
     */
    private $stack;

    /**
     * @var Identifier[]
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
     * @param Constant[] $constants
     * @param Identifier[] $identifiers
     */
    public function __construct(array $RPNCode, array $constants, array $identifiers)
    {
        $this->RPNCode = $RPNCode;
        $this->constants = $constants;
        $this->identifiers = $identifiers;
        $this->stack = new SplStack();
    }

    public function process(): void
    {
        $step = 0;
        foreach ($this->RPNCode as $instruction) {
            $this->instruction($instruction);
            echo "Step $step" . PHP_EOL;
            echo 'Id   Name       Type       Value' . PHP_EOL;
            foreach ($this->getIdentifiers() as $identifier) {
                echo $identifier . PHP_EOL;
            }
            echo PHP_EOL;
            $step++;
        }
    }

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

    private function binaryOperator(BinaryOperator $operator): void
    {
        $left = $this->stack->pop();
        $right = $this->stack->pop();

        if ($this->isAddOp($operator) || $this->isMultOp($operator)) {
            $result = $this->calculate($operator, $left, $right);
            $this->stack->push($result);
        } elseif ($operator->getType() === 'AssignOp') {
            /** @var $left Identifier */
            $left->setValue($right->getValue());
            $this->identifiers[$left->getId()] = $left;
        } else {
            throw new RuntimeException('Unknown binary operator ' . $operator->getType());
        }
    }

    private function isAddOp(BinaryOperator $operator): bool
    {
        return $operator->getType() === 'Plus' || $operator->getType() === 'Minus';
    }

    private function isMultOp(BinaryOperator $operator): bool
    {
        return $operator->getType() === 'Star' || $operator->getType() === 'Slash';
    }

    private function calculate(BinaryOperator $operator, $left, $right): Constant
    {
        if ($operator->getType() === 'Plus') {
            $value = $left->getValue() + $right->getValue();
            if ($left->getType() === 'real' || $right->getType() === 'real') {
                $type = 'real';
            } else {
                $type = 'int';
            }
        } elseif ($operator->getType() === 'Minus') {
            $value = $left->getValue() - $right->getValue();
            if ($left->getType() === 'real' || $right->getType() === 'real') {
                $type = 'real';
            } else {
                $type = 'int';
            }
        } elseif ($operator->getType() === 'Star') {
            $value = $left->getValue() * $right->getValue();
            $type = 'real';
        } elseif ($operator->getType() === 'Slash') {
            $value = $left->getValue() / $right->getValue();
            $type = 'real';
        } else {
            throw new RuntimeException('Unknown arithmetic operator ' . $operator->getType());
        }

        return new Constant(200, $value, $type);

    }

    /**
     * @return Identifier[]
     */
    public function getIdentifiers(): array
    {
        return $this->identifiers;
    }

    /**
     * @return Constant[]
     */
    public function getConstants(): array
    {
        return $this->constants;
    }
}