<?php


namespace Z99Compiler\Entity;


use JsonSerializable;

class Identifier implements JsonSerializable
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $type;

    /**
     * @var string|null
     */
    private $value;

    public function __construct(int $id, string $name, ?string $type, ?string $value)
    {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return sprintf('@%02d  %-10s %-10s %-10s',
            $this->getId(),
            $this->getName(),
            $this->getType(),
            $this->getValue() ?: 'Undefined'
        );
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @param string|null $value
     */
    public function setValue(?string $value): void
    {
        $this->value = $value;
    }

    public function jsonSerialize()
    {
        return [
            'object' => 'Identifier',
            'id' => $this->getId(),
            'name' => $this->getName(),
            'type' => $this->getType(),
            'value' => $this->getValue()
        ];
    }
}