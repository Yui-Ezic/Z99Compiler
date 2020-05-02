<?php


namespace SemanticAnalyzer\Handlers;


use RuntimeException;
use Z99Compiler\Entity\Constant;
use Z99Compiler\Entity\Tree\Node;

class ConstantsTableHandler extends AbstractHandler
{
    /**
     * @var Constant[]
     */
    private $constants = [];

    /**
     * @var int
     */
    private $constantId = 0;

    public function handle(Node $node): void
    {
        $this->constants = [];
        $this->statementList($node);
    }

    /**
     * @return Constant[]
     */
    public function getConstants(): array
    {
        return $this->constants;
    }

    private function statementList(Node $node): void
    {
        $children = $this->getChildrenOrFail($node);

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
            $contants = [$node];
            return $contants;
        }

        $contants = [];

        if (($children = $node->getChildren()) !== null) {
            foreach ($children as $child) {
                $array = $this->findConstants($child);
                foreach ($array as $item) {
                    $contants[] = $item;
                }
            }
        }

        return $contants;
    }

    public function constant(Node $node): void
    {
        $type = $this->getTypeFromConstant($node);
        $value = $node->getChildren()[0]->getChildren()[0]->getName();

        $this->addConstant($value, $type);
    }

    public function getTypeFromConstant(Node $node): string
    {
        $type = $node->getChildren()[0]->getName();

        if ($type === 'IntNum') {
            return 'int';
        }

        if ($type === 'RealNum') {
            return 'real';
        }

        throw new RuntimeException('Unknown constant type ' . $type);
    }

    public function addConstant($value, $type): void
    {
        if ($this->isConstantInArray($value)) {
            return;
        }

        $constant = new Constant($this->constantId, $value, $type);
        $this->constants[$this->constantId] = $constant;
        $this->constantId++;
    }

    public function isConstantInArray($value): bool
    {
        foreach ($this->constants as $constant) {
            if ($constant->getValue() === $value) {
                return true;
            }
        }

        return false;
    }
}