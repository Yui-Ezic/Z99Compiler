<?php


namespace SemanticAnalyzer\Handlers;


use Z99Compiler\Entity\Tree\Node;
use Z99Compiler\Entity\Tree\Tree;
use Z99Compiler\Tables\IdentifiersTable;

class DeclareListHandler extends AbstractHandler
{
    /**
     * @var IdentifiersTable
     */
    private $identifiers;

    public function __construct()
    {
        $this->identifiers = new IdentifiersTable();
    }

    /**
     * @param Node $node
     */
    public function handle(Node $node): void
    {
        $this->declareList($node);
    }

    /**
     * @param Node $node
     */
    protected function declareList(Node $node): void
    {
        $children = Tree::getChildrenOrFail($node);

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
        $children = Tree::getChildrenOrFail($node);

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
            $this->identifiers->addIdentifier($identifier, $type);
            //$this->addIdentifier($identifier, $type);
        }
    }

    /**
     * @param Node $node
     * @return array
     */
    protected function identList(Node $node): array
    {
        $children = Tree::getChildrenOrFail($node);
        $identifiers = [];

        foreach ($children as $child) {
            if ($child->getName() === 'Ident') {
                $identifiers[] = $child->getChildren()[0]->getName();
            }
        }

        return $identifiers;
    }

    /**
     * @return IdentifiersTable
     */
    public function getIdentifiers(): IdentifiersTable
    {
        return $this->identifiers;
    }
}