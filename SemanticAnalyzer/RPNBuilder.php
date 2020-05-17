<?php


namespace SemanticAnalyzer;


use RuntimeException;
use Z99Compiler\Entity\BinaryOperator;
use Z99Compiler\Entity\Constant;
use Z99Compiler\Entity\Identifier;
use Z99Compiler\Entity\Label;
use Z99Compiler\Entity\Tree\Node;
use Z99Compiler\Entity\Tree\Tree;
use Z99Compiler\Entity\UnaryOperator;
use Z99Compiler\Tables\ConstantsTable;
use Z99Compiler\Tables\IdentifiersTable;
use Z99Compiler\Tables\LabelsTable;

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
     * @var LabelsTable
     */
    private $labelsTable;

    /**
     * @var int
     */
    private $labelNum = 0;

    /**
     * RPNBuilder constructor.
     * @param IdentifiersTable $identifiers
     * @param ConstantsTable $constants
     * @param LabelsTable $labelsTable
     */
    public function __construct(IdentifiersTable $identifiers, ConstantsTable $constants, LabelsTable $labelsTable)
    {
        $this->identifiers = $identifiers;
        $this->constants = $constants;
        $this->labelsTable = $labelsTable;
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

    /**
     * Handle statementList rule
     * "statement (Semi statement)*"
     * @param Node $statementList
     */
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

    /**
     * Handle statement rule
     * " assign
     * | input
     * | output
     * | branchStatement
     * | repeatStatement"
     * @param Node $statement
     */
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
        /**
         * ~start~ statementList
         * boolExpr startLabel JF
         * endLabel Jump ~end~
         */
        $children = Tree::getChildrenOrFail($repeatStatement);
        $start = count($this->RPNCode);
        $this->statementList($children[1]);
        $this->boolExpr($children[4]);
        $endLabel = $this->RPNCode[] = new Label($this->generateLabelName());
        $this->RPNCode[] = new BinaryOperator('jumpIf', 'JF');
        $startLabel = $this->RPNCode[] = new Label($this->generateLabelName());
        $this->RPNCode[] = new UnaryOperator('jump', 'Jump');
        $end = count($this->RPNCode);
        $this->labelsTable->add($startLabel, $start);
        $this->labelsTable->add($endLabel, $end);
    }

    /**
     * Generate unique name for label
     * @return string
     */
    private function generateLabelName(): string
    {
        return 'l' . $this->labelNum++;
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
        $endLabel = $this->RPNCode[] = new Label($this->generateLabelName());
        $this->RPNCode[] = new BinaryOperator('jumpIf', 'JF');
        $this->statementList($children[3]);
        $end = count($this->RPNCode);
        $this->labelsTable->add($endLabel, $end);
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

    /**
     * Handle expression rule
     * "boolExpr | arithmExpression"
     * @param Node $node
     */
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

    /**
     * Handle boolExpr rule
     * "arithmExpression RelOp arithmExpression"
     * @param Node $node
     */
    public function boolExpr(Node $node): void
    {
        $children = Tree::getChildrenOrFail($node);

        $this->arithmExpression($children[0]);
        $this->arithmExpression($children[2]);
        $this->RPNCode[] = $this->relOp($children[1]);
    }

    /**
     * Handle arithmExpression rule
     * "term addOp arithmExpression | term"
     * @param Node $node
     */
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

    /**
     * Handle term rule
     * "signedFactor multOp term | signedFactor"
     * @param Node $node
     */
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

    /**
     * Handle signedFactor rule
     * "addOp? factor"
     * @param Node $signedFactor
     */
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

    /**
     * Handle factor rule
     * " Ident
     * | constant
     * | LBracket arithmExpression RBracket"
     * @param Node $node
     */
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

    /**
     * Handle ident
     * @param Node $node
     * @return Identifier
     */
    public function ident(Node $node): Identifier
    {
        $child = $node->getFirstChild();
        return $this->findIdentifier($child);
    }

    /**
     * Find identifier in table or fail
     * @param Node $node
     * @return Identifier
     */
    private function findIdentifier(Node $node): Identifier
    {
        if (($identifier = $this->identifiers->findByName($node->getName())) !== null) {
            return $identifier;
        }

        throw new RuntimeException('Cannot find variable ' . $node->getName() . ' in identifiers table.' . PHP_EOL .
            'In line ' . $node->getLine());
    }

    /**
     * Handle constant rule
     * " IntNum
     * | RealNum
     * | BoolConst"
     * @param Node $node
     */
    public function constant(Node $node): void
    {
        $child = $node->getFirstChild();
        $value = $child->getFirstChild();
        $this->RPNCode[] = $this->findConstant($value);
    }

    /**
     * Find constant in table or fail
     * @param Node $node
     * @return Constant
     */
    private function findConstant(Node $node): Constant
    {
        if (($constant = $this->constants->find($node->getName())) !== null) {
            return $constant;
        }

        throw new RuntimeException('Cannot find constant ' . $node->getName() . ' in constant table.' . PHP_EOL .
            'In line ' . ($node->getLine() ?: ''));
    }

    /**
     * Handle unary Plus and Minus
     * @param Node $addOp
     */
    private function unaryAddOp(Node $addOp): void
    {
        $type = $addOp->getFirstChild()->getName();
        $operator = $addOp->getFirstChild()->getFirstChild()->getName();
        $this->RPNCode[] = new UnaryOperator($operator, $type);
    }

    /**
     * Handle binary Star and Slash operations
     * @param Node $node
     * @return BinaryOperator
     */
    public function multOp(Node $node): BinaryOperator
    {
        $operator = $node->getFirstChild()->getFirstChild()->getName();
        $type = $node->getFirstChild()->getName();
        return new BinaryOperator($operator, $type);
    }

    /**
     * Handle binary Plus and Minus
     * @param Node $node
     * @return BinaryOperator
     */
    public function addOp(Node $node): BinaryOperator
    {
        $operator = $node->getFirstChild()->getFirstChild()->getName();
        $type = $node->getFirstChild()->getName();
        return new BinaryOperator($operator, $type);
    }

    /**
     * Handle binary relations operation
     * @param Node $node
     * @return BinaryOperator
     */
    public function relOp(Node $node): BinaryOperator
    {
        $type = $node->getName();
        $operator = $node->getFirstChild()->getName();
        return new BinaryOperator($operator, $type);
    }

    /**
     * Handle binary assign operation
     * @param Node $node
     * @return BinaryOperator
     */
    public function assignOp(Node $node): BinaryOperator
    {
        $type = $node->getName();
        $operator = $node->getFirstChild()->getName();
        return new BinaryOperator($operator, $type);
    }
}