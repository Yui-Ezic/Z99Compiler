<?php


namespace Z99Compiler\Entity\Tree;


class TreeBuilder
{
    public static function fromJson($parserTree): Node
    {
        $tree = new Node($parserTree['name']);

        foreach ($parserTree['children'] as $child) {
            $tree->addChild(self::fromJson($child));
        }

        return $tree;
    }
}