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
     * @var int|null
     */
    private $line;

    /**
     * Node constructor.
     * @param string $name
     * @param int|null $line
     */
    public function __construct(string $name, ?int $line = null)
    {
        $this->name = $name;
        $this->children = [];
        $this->line = $line;
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
            'line' => $this->getLine(),
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

    /**
     * @return int|null
     */
    public function getLine(): ?int
    {
        return $this->line;
    }
}