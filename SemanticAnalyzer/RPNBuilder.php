<?php


namespace SemanticAnalyzer;


use RuntimeException;
use Z99Compiler\Entity\BinaryOperator;
use Z99Compiler\Entity\Constant;
use Z99Compiler\Entity\Identifier;
use Z99Compiler\Entity\JumpIf;
use Z99Compiler\Entity\Tree\Node;
use Z99Compiler\Entity\Tree\Tree;
use Z99Compiler\Entity\UnaryOperator;
use Z99Compiler\Tables\ConstantsTable;
use Z99Compiler\Tables\IdentifiersTable;

class RPNBuilder
{
    /**
     * @var IdentifiersTable
     */
    private $identifiers;

    /**
     * @var ConstantsTable
     */
    private $constants;

    /**
     * @var array
     */
    private $RPNCode = [];

    /**
     * RPNBuilder constructor.
     * @param IdentifiersTable $identifiers
     * @param ConstantsTable $constants
     */
    public function __construct(IdentifiersTable $identifiers, ConstantsTable $constants)
    {
        $this->identifiers = $identifiers;
        $this->constants = $constants;
    }

    /**
     * @param Node $root
     * @return array RPNCode
     */
    public function buildRPN(Node $root): array
    {
        $statementList = Tree::findFirstOrFail('statementList', $root);
        $this->statementList($statementList);
        return $this->RPNCode;
    }

    private function statementList(Node $statementList): void
    {
        if ($statementList->getName() !== 'statementList') {
            throw new RuntimeException('Unexpected ' . $statementList->getName() . ' instead of statementList.');
        }
        $statements = Tree::getChildrenOrFail($statementList);
        foreach ($statements as $statement) {
            if ($statement->getFirstChild()->getName() !== ';') {
                $this->statement($statement);
            }
        }
    }

    private function statement(Node $statement): void
    {
        $child = $statement->getFirstChild();
        if ($child->getName() === 'assign') {
            $this->assign($child);
        } elseif ($child->getName() === 'branchStatement') {
            $this->branchStatement($child);
        } elseif ($child->getName() === 'repeatStatement') {
            $this->repeatStatement($child);
        } elseif ($child->getName() === 'output') {
            $this->output($child);
        } elseif ($child->getName() === 'input') {
            $this->input($child);
        } else {
            throw new RuntimeException('Unknown statement ' . $child->getName());
        }
    }

    /**
     * Handle input statement
     * "Input LBracket identList RBracket"
     * @param Node $input
     */
    public function input(Node $input): void
    {
        $children = Tree::getChildrenOrFail($input);
        $identList = $children[2];
        foreach ($identList->getChildren() as $item) {
            if ($item->getName() === 'Ident') {
                $this->RPNCode[] = $this->ident($item);
                $this->RPNCode[] = new UnaryOperator('read', 'Input');
            }
        }
    }

    /**
     * Handle output statement
     * "Write LBracket identList RBracket"
     * @param Node $output
     */
    public function output(Node $output): void
    {
        $children = Tree::getChildrenOrFail($output);
        $identList = $children[2];
        foreach ($identList->getChildren() as $item) {
            if ($item->getName() === 'Ident') {
                $this->RPNCode[] = $this->ident($item);
                $this->RPNCode[] = new UnaryOperator('write', 'Output');
            }
        }
    }

    /**
     * Handle repeat Statement
     * "Repeat statementList Semi Until boolExpr"
     * @param Node $repeatStatement
     */
    public function repeatStatement(Node $repeatStatement): void
    {
        $children = Tree::getChildrenOrFail($repeatStatement);
        $start = count($this->RPNCode);
        $this->statementList($children[1]);
        $this->boolExpr($children[4]);
        $jf = $this->RPNCode[] = new JumpIf();
        $this->RPNCode[] = $this->constants->addConstant('false', 'bool');
        $this->RPNCode[] = new JumpIf($start);
        $end = count($this->RPNCode);
        $jf->setAddress($end);
    }

    /**
     * Handle branch Statement
     * "If expression Then statementList Semi Fi"
     * @param Node $branchStatement
     */
    public function branchStatement(Node $branchStatement): void
    {
        $children = Tree::getChildrenOrFail($branchStatement);
        $this->expression($children[1]);
        $jf = $this->RPNCode[] = new JumpIf();
        $this->statementList($children[3]);
        $jf->setAddress(count($this->RPNCode));
    }

    /**
     * Handle assign statement
     * "Ident AssignOp expression"
     * @param Node $node
     */
    public function assign(Node $node): void
    {
        $children = Tree::getChildrenOrFail($node);

        $this->RPNCode[] = $this->ident($children[0]);
        $this->expression($children[2]);
        $this->RPNCode[] = $this->assignOp($children[1]);
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

        $this->arithmExpression($children[0]);
        $this->arithmExpression($children[2]);
        $this->RPNCode[] = $this->relOp($children[1]);
    }

    public function arithmExpression(Node $node): void
    {
        $children = Tree::getChildrenOrFail($node);

        if (Tree::hasChild('addOp', $node)) {
            $this->term($children[0]);
            $this->arithmExpression($children[2]);
            $this->RPNCode[] = $this->addOp($children[1]);
            return;
        }

        $this->term($children[0]);
    }

    public function term(Node $node): void
    {
        $children = Tree::getChildrenOrFail($node);

        if (Tree::hasChild('multOp', $node)) {
            $this->signedFactor($children[0]);
            $this->term($children[2]);
            $this->RPNCode[] = $this->multOp($children[1]);
            return;
        }

        $this->signedFactor($children[0]);
    }

    public function signedFactor(Node $signedFactor): void
    {
        $children = $signedFactor->getChildren();

        if (count($children) === 2) {
            $this->factor($children[1]);
            $this->unaryAddOp($children[0]);
        } else {
            $this->factor($children[0]);
        }
    }

    public function factor(Node $node): void
    {
        $children = Tree::getChildrenOrFail($node);

        if (Tree::hasChild('arithmExpression', $node)) {
            $this->arithmExpression($children[1]);
        } elseif (Tree::hasChild('Ident', $node)) {
            $this->RPNCode[] = $this->ident($children[0]);
        } else {
            $this->constant($children[0]);
        }
    }

    public function ident(Node $node): Identifier
    {
        $child = $node->getFirstChild();
        return $this->findIdentifier($child);
    }

    private function findIdentifier(Node $node): Identifier
    {
        if (($identifier = $this->identifiers->findByName($node->getName())) !== null) {
            return $identifier;
        }

        throw new RuntimeException('Cannot find variable ' . $node->getName() . ' in identifiers table.' . PHP_EOL .
            'In line ' . $node->getLine());
    }

    public function constant(Node $node): void
    {
        $child = $node->getFirstChild();
        $value = $child->getFirstChild();
        $this->RPNCode[] = $this->findConstant($value);
    }

    private function findConstant(Node $node): Constant
    {
        if (($constant = $this->constants->find($node->getName())) !== null) {
            return $constant;
        }

        throw new RuntimeException('Cannot find constant ' . $node->getName() . ' in constant table.' . PHP_EOL .
            'In line ' . ($node->getLine() ?: ''));
    }

    private function unaryAddOp(Node $addOp): void
    {
        $type = $addOp->getFirstChild()->getName();
        $operator = $addOp->getFirstChild()->getFirstChild()->getName();
        $this->RPNCode[] = new UnaryOperator($operator, $type);
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

    public function relOp(Node $node): BinaryOperator
    {
        $type = $node->getName();
        $operator = $node->getFirstChild()->getName();
        return new BinaryOperator($operator, $type);
    }

    public function assignOp(Node $node): BinaryOperator
    {
        $type = $node->getName();
        $operator = $node->getFirstChild()->getName();
        return new BinaryOperator($operator, $type);
    }
}