<?php


namespace Z99Compiler\Entity\Tree;


class TreeBuilder
{
    public static function fromJson($parserTree, $root): Node
    {
        $tree = new Node($root);

        if (is_array($parserTree))
        {
            foreach ($parserTree as $name => $item)
            {
                if (is_numeric($name)) {
                    foreach ($item as $key => $value) {
                        $tree->addChild(self::fromJson($value, $key));
                    }
                } else {
                    $tree->addChild(self::fromJson($item, $name));
                }
            }
        } elseif (!empty($parserTree)) {
            $tree->addChild(new Node($parserTree));
        }

        return $tree;
    }
}