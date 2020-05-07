<?php


namespace Z99Compiler\Entity\Tree;


use JsonSerializable;

class Node implements JsonSerializable
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Node[]|null
     */
    private $children;

    /**
     * Node constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->children = [];
    }

    /**
     * @return Node
     */
    public function getFirstChild(): Node
    {
        return $this->getChildren()[0];
    }

    /**
     * @return Node[]|null
     */
    public function getChildren(): ?array
    {
        return $this->children;
    }

    /**
     * @param Node[] $children
     */
    public function addChildren(array $children): void
    {
        foreach ($children as $child) {
            $this->addChild($child);
        }
    }

    /**
     * @param Node $child
     */
    public function addChild(Node $child): void
    {
        $this->children[] = $child;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'name' => $this->getName(),
            'children' => $this->getChildren()
        ];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}