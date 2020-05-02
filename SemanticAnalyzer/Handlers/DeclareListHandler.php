<?php


namespace SemanticAnalyzer\Handlers;


use RuntimeException;
use Z99Compiler\Entity\Identifier;
use Z99Compiler\Entity\Tree\Node;

class DeclareListHandler extends AbstractHandler
{
    /**
     * @var Identifier[]
     */
    private $identifiers = [];

    /**
     * @var int
     */
    private $identifierId = 0;

    /**
     * @param Node $node
     */
    public function handle(Node $node): void
    {
        $this->identifiers = [];
        $this->declareList($node);
    }

    /**
     * @param Node $node
     */
    protected function declareList(Node $node): void
    {
        $children = $this->getChildrenOrFail($node);

        foreach ($children as $child) {
            if ($child->getName() === 'declaration') {
                $this->declaration($child);
            }
        }
    }

    /**
     * @param Node $node
     */
    protected function declaration(Node $node): void
    {
        $children = $this->getChildrenOrFail($node);

        $type = null;
        $identifiers = [];

        foreach ($children as $child) {
            if ($child->getName() === 'identList') {
                $identifiers = $this->identList($child);
            } elseif ($child->getName() === 'Type') {
                $type = $child->getChildren()[0]->getName();
            }
        }

        foreach ($identifiers as $identifier) {
            $this->addIdentifier($identifier, $type);
        }
    }

    /**
     * @param Node $node
     * @return array
     */
    protected function identList(Node $node): array
    {
        $children = $this->getChildrenOrFail($node);
        $identifiers = [];

        foreach ($children as $child) {
            if ($child->getName() === 'Ident') {
                $identifiers[] = $child->getChildren()[0]->getName();
            }
        }

        return $identifiers;
    }

    /**
     * @param $name
     * @param $type
     * @return int
     */
    private function addIdentifier($name, $type): int
    {
        if (($id = $this->findIdentifier($name)) !== null) {
            throw new RuntimeException('Duplicate identifier ' . $name);
        }

        $id = $this->identifierId++;
        $this->identifiers[$id] = new Identifier($id, $name, $type, null);

        return $id;
    }

    /**
     * Returns id of identifier if find else null
     *
     * @param $name
     * @return int|null
     */
    private function findIdentifier($name): ?int
    {
        $array = array_map(static function (Identifier $identifier) {
            return $identifier->getName();
        }, $this->identifiers);

        if (($id = array_search($name, $array, true)) !== false) {
            return $id;
        }

        return null;
    }

    /**
     * @return Identifier[]
     */
    public function getIdentifiers(): array
    {
        return $this->identifiers;
    }
}