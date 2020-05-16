<?php


namespace Z99Compiler\Entity;


use JsonSerializable;

class Label implements JsonSerializable
{
    /**
     * @var string
     */
    private $name;

    /**
     * Label constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'object' => 'Label',
            'name' => $this->getName()
        ];
    }
}