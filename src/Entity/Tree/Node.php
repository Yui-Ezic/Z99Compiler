<?php


namespace Z99Compiler\Entity\Tree;


class Node
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Node[]|null
     */
    private $children;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->children = [];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Node[]|null
     */
    public function getChildren(): ?array
    {
        return $this->children;
    }

    /**
     * @param Node $children
     */
    public function addChild(Node $children): void
    {
        $this->children[] = $children;
    }
}