<?php


namespace Z99Compiler\Entity\Tree;


use RuntimeException;

class Tree
{
    /**
     * @param Node $tree
     * @param $file
     * @param int $level
     */
    public static function printToFile(Node $tree, $file, $level = 0)
    {
        $string = str_repeat('--', $level) . $tree->getName() . PHP_EOL;
        fwrite($file, $string);
        $level++;
        foreach ($tree->getChildren() as $children)
        {
            self::printToFile($children, $file, $level);
        }
    }

    /**
     * @param $name
     * @param Node $node
     * @return Node|null
     */
    public static function findFirst($name, Node $node): ?Node
    {
        if ($node->getName() === $name) {
            return $node;
        }

        if ($children = $node->getChildren()) {
            foreach ($children as $child) {
                if (static::findFirst($name, $child)) {
                    return $child;
                }
            }
        }

        return null;
    }

    public static function findFirstOrFail($name, Node $node): Node
    {
        if (($result = static::findFirst($name, $node)) !== null) {
            return $result;
        }

        throw new RuntimeException('Cannot find node with name ' . $name . ' in tree ' . $node->getName());
    }

    /**
     * @param string $name
     * @param Node $node
     * @return bool
     */
    public static function hasChild(string $name, Node $node): bool
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
    public static function getChildrenOrFail(Node $node): array
    {
        if ($children = $node->getChildren())
        {
            return $children;
        }

        throw new RuntimeException('Node' . $node->getName() . ' hasn\'t children.');
    }

    /**
     * @param $name
     * @param Node $node
     * @return Node[]
     */
    public static function findAll($name, Node $node): array
    {
        $nodes = [];
        if ($node->getName() === $name) {
            $nodes[] = $node;
        }

        if ($children = $node->getChildren()) {
            foreach ($children as $child) {
                foreach (static::findAll($name, $child) as $item) {
                    $nodes[] = $item;
                }
            }
        }

        return $nodes;
    }
}