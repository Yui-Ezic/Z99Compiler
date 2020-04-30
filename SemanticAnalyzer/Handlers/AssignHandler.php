<?php


namespace SemanticAnalyzer\Handlers;


use RuntimeException;
use SemanticAnalyzer\BinaryOperator;
use SemanticAnalyzer\Constant;
use SemanticAnalyzer\Identifier;
use SemanticAnalyzer\Tree\Node;

class AssignHandler extends AbstractHandler
{
    /**
     * @var array
     */
    private $result = [];

    /**
     * @var Constant[]
     */
    private $constants;

    /**
     * @var Identifier[]
     */
    private $identifiers;

    public function __construct(array $identifiers, array $constants)
    {
        $this->identifiers = $identifiers;
        $this->constants = $constants;
    }

    public function handle(Node $node): array
    {
        $this->result = [];
        $this->assign($node);
        return $this->result;
    }

    public function assign(Node $node): void
    {
        $children = $this->getChildrenOrFail($node);

        $this->expression($children[2]);
        $this->result[] = $this->ident($children[0]);
        $this->result[] = $this->assignOp($children[1]);
    }

    public function expression(Node $node): void
    {
        $children = $this->getChildrenOrFail($node);

        if ($children[0]->getName() === 'boolExpr') {
            $this->boolExpr($children[0]);
        } elseif ($children[0]->getName() === 'arithmExpression') {
            $this->arithmExpression($children[0]);
        } else {
            throw new RuntimeException('Invalid children' . $children[0]->getName() . ' in expression.');
        }
    }

    public function boolExpr(Node $node): void
    {
        $children = $this->getChildrenOrFail($node);

        $this->arithmExpression($children[2]);
        $this->arithmExpression($children[0]);
        $this->result[] = $children[2]->getName();
    }

    public function arithmExpression(Node $node): void
    {
        $children = $this->getChildrenOrFail($node);

        if ($this->has('addOp', $node)) {
            $this->arithmExpression($children[2]);
            $this->term($children[0]);
            $this->result[] = $this->addOp($children[1]);
            return;
        }

        $this->term($children[0]);
    }

    public function term(Node $node): void
    {
        $children = $this->getChildrenOrFail($node);

        if ($this->has('multOp', $node)) {
            $this->term($children[2]);
            $this->factor($children[0]);
            $this->result[] = $this->multOp($children[1]);
            return;
        }

        $this->factor($children[0]);
    }

    public function factor(Node $node): void
    {
        $children = $this->getChildrenOrFail($node);

        if ($this->has('arithmExpression', $node)) {
            $this->arithmExpression($children[1]);
        } elseif ($this->has('Ident', $node)) {
            $this->result[] = $this->ident($children[0]);
        } else {
            $this->result[] = $this->constant($children[0]);
        }
    }

    public function constant(Node $node): Constant
    {
        $value = $node->getChildren()[0]->getChildren()[0]->getName();
        return $this->findConstant($value);
    }

    public function multOp(Node $node): BinaryOperator
    {
        $operator = $node->getChildren()[0]->getChildren()[0]->getName();
        $type = $node->getChildren()[0]->getName();
        return new BinaryOperator($operator, $type);
    }

    public function addOp(Node $node): BinaryOperator
    {
        $operator = $node->getChildren()[0]->getChildren()[0]->getName();
        $type = $node->getChildren()[0]->getName();
        return new BinaryOperator($operator, $type);
    }

    public function assignOp(Node $node): BinaryOperator
    {
        $type = $node->getName();
        $operator = $node->getChildren()[0]->getName();
        return new BinaryOperator($operator, $type);
    }

    public function ident(Node $node): Identifier
    {
        $name = $node->getChildren()[0]->getName();
        return $this->findIdentifier($name);
    }

    private function findConstant($value) : Constant
    {
        foreach ($this->constants as $constant) {
            if ($constant->getValue() === $value) {
                return $constant;
            }
        }

        throw new RuntimeException('Cannot find constant ' . $value , ' in constant table.');
    }

    private function findIdentifier($name): Identifier
    {
        foreach ($this->identifiers as $identifier) {
            if ($identifier->getName() === $name) {
                return $identifier;
            }
        }

        throw new RuntimeException('Cannot find variable ' . $name , ' in constant table.');
    }
}