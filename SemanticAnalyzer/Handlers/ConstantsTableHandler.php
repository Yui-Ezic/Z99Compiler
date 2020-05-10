<?php


namespace SemanticAnalyzer\Handlers;


use RuntimeException;
use Z99Compiler\Entity\Tree\Node;
use Z99Compiler\Entity\Tree\Tree;
use Z99Compiler\Tables\ConstantsTable;

class ConstantsTableHandler extends AbstractHandler
{
    /**
     * @var ConstantsTable
     */
    private $constants = [];

    public function handle(Node $node): void
    {
        $this->constants = new ConstantsTable();
        $this->statementList($node);
    }

    /**
     * @return ConstantsTable
     */
    public function getConstants(): ConstantsTable
    {
        return $this->constants;
    }

    private function statementList(Node $node): void
    {
        $children = Tree::getChildrenOrFail($node);

        foreach ($children as $child) {
            if ($child->getName() === 'statement') {
                $this->statement($child);
            }
        }
    }

    private function statement(Node $node): void
    {
        $constants = $this->findConstants($node);

        foreach ($constants as $constant) {
            $this->constant($constant);
        }
    }

    private function findConstants(Node $node): array
    {
        if ($node->getName() === 'constant') {
            return [$node];
        }

        $constants = [];

        if (($children = $node->getChildren()) !== null) {
            foreach ($children as $child) {
                $array = $this->findConstants($child);
                foreach ($array as $item) {
                    $constants[] = $item;
                }
            }
        }

        return $constants;
    }

    public function constant(Node $node): void
    {
        $child = $node->getFirstChild();
        $name = $child->getName();
        if ($name === 'intNum' || $name === 'realNum') {
            $this->intOrRealNum($child);
            return;
        }
        $type = $this->nodeToType($child);
        $value = $child->getFirstChild()->getName();

        $this->constants->addConstant($value, $type);
    }

    public function intOrRealNum(Node $node): void
    {
        $children = Tree::getChildrenOrFail($node);
        if (count($children) === 2) {
            $num = $children[1];
        } else {
            $num = $children[0];
        }

        $type = $this->nodeToType($num);
        $value = $num->getFirstChild()->getName();

        $this->constants->addConstant($value, $type);
    }

    public function nodeToType(Node $node): string
    {
        $type = $node->getName();

        if ($type === 'UnsignedInt') {
            return 'int';
        }

        if ($type === 'UnsignedReal') {
            return 'real';
        }

        if ($type === 'BoolConst') {
            return 'bool';
        }

        throw new RuntimeException('Unknown constant type ' . $type);
    }
}