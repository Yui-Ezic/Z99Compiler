<?php


namespace SemanticAnalyzer\Handlers;

use RuntimeException;
use Z99Compiler\Entity\Tree\Node;

abstract class AbstractHandler
{
    /**
     * @param string $name
     * @param Node $node
     * @return bool
     */
    protected function has(string $name, Node $node): bool
    {
        $children = $node->getChildren();

        foreach ($children as $child) {
            if ($child->getName() === $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Node $node
     * @return Node[]
     */
    protected function getChildrenOrFail(Node $node): array
    {
        if ($children = $node->getChildren())
        {
            return $children;
        }

        throw new RuntimeException('Invalid parser tree.');
    }
}