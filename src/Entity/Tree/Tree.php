<?php


namespace Z99Compiler\Entity\Tree;


class Tree
{
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
}