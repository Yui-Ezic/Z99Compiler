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
     * @param Node $child
     */
    public function addChild(Node $child): void
    {
        $this->children[] = $child;
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

    public function jsonSerialize()
    {
        return [
            'name' => $this->getName(),
            'children' => $this->getChildren()
        ];
    }
}