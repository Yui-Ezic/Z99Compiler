<?php


namespace SemanticAnalyzer\Handlers;


use RuntimeException;
use Z99Compiler\Entity\BinaryOperator;
use Z99Compiler\Entity\Constant;
use Z99Compiler\Entity\Identifier;
use Z99Compiler\Entity\Tree\Node;
use Z99Compiler\Entity\Tree\Tree;
use Z99Compiler\Tables\ConstantsTable;
use Z99Compiler\Tables\IdentifierTable;

class AssignHandler extends AbstractHandler
{
    /**
     * @var array
     */
    private $result = [];

    /**
     * @var ConstantsTable
     */
    private $constants;

    /**
     * @var IdentifierTable
     */
    private $identifiers;

    public function __construct(IdentifierTable $identifiers, ConstantsTable $constants)
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
        $children = Tree::getChildrenOrFail($node);

        $this->expression($children[2]);
        $this->result[] = $this->ident($children[0]);
        $this->result[] = $this->assignOp($children[1]);
    }

    public function expression(Node $node): void
    {
        $children = Tree::getChildrenOrFail($node);

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
        $children = Tree::getChildrenOrFail($node);

        $this->arithmExpression($children[2]);
        $this->arithmExpression($children[0]);
        $this->result[] = $this->relOp($children[1]);
    }

    public function arithmExpression(Node $node): void
    {
        $children = Tree::getChildrenOrFail($node);

        if (Tree::hasChild('addOp', $node)) {
            $this->arithmExpression($children[2]);
            $this->term($children[0]);
            $this->result[] = $this->addOp($children[1]);
            return;
        }

        $this->term($children[0]);
    }

    public function term(Node $node): void
    {
        $children = Tree::getChildrenOrFail($node);

        if (Tree::hasChild('multOp', $node)) {
            $this->term($children[2]);
            $this->factor($children[0]);
            $this->result[] = $this->multOp($children[1]);
            return;
        }

        $this->factor($children[0]);
    }

    public function factor(Node $node): void
    {
        $children = Tree::getChildrenOrFail($node);

        if (Tree::hasChild('arithmExpression', $node)) {
            $this->arithmExpression($children[1]);
        } elseif (Tree::hasChild('Ident', $node)) {
            $this->result[] = $this->ident($children[0]);
        } else {
            $this->result[] = $this->constant($children[0]);
        }
    }

    public function constant(Node $node): Constant
    {
        $value = $node->getFirstChild()->getFirstChild()->getName();
        return $this->findConstant($value);
    }

    public function multOp(Node $node): BinaryOperator
    {
        $operator = $node->getFirstChild()->getFirstChild()->getName();
        $type = $node->getFirstChild()->getName();
        return new BinaryOperator($operator, $type);
    }

    public function addOp(Node $node): BinaryOperator
    {
        $operator = $node->getFirstChild()->getFirstChild()->getName();
        $type = $node->getFirstChild()->getName();
        return new BinaryOperator($operator, $type);
    }

    public function assignOp(Node $node): BinaryOperator
    {
        $type = $node->getName();
        $operator = $node->getFirstChild()->getName();
        return new BinaryOperator($operator, $type);
    }

    public function relOp(Node $node): BinaryOperator
    {
        $type = $node->getName();
        $operator = $node->getFirstChild()->getName();
        return new BinaryOperator($operator, $type);
    }

    public function ident(Node $node): Identifier
    {
        $name = $node->getFirstChild()->getName();
        return $this->findIdentifier($name);
    }

    private function findConstant($value) : Constant
    {
        if (($constant = $this->constants->find($value)) !== null) {
            return $constant;
        }

        throw new RuntimeException('Cannot find constant ' . $value , ' in constant table.');
    }

    private function findIdentifier($name): Identifier
    {
        if (($identifier = $this->identifiers->findByName($name)) !== null) {
            return $identifier;
        }

        throw new RuntimeException('Cannot find variable ' . $name . ' in constant table.');
    }
}