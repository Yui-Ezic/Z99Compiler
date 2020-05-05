<?php


namespace Z99Compiler\Entity;


use JsonSerializable;

class UnaryOperator implements JsonSerializable
{
    /**
     * @var string
     */
    private $operator;

    /**
     * @var string
     */
    private $type;

    public function __construct(string $operator, string $type)
    {
        $this->operator = $operator;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    public function jsonSerialize()
    {
        return [
            'object' => 'UnaryOperator',
            'operator' => $this->getOperator(),
            'type' => $this->getType()
        ];
    }

    public static function fromArray(array $array): self
    {
        return new static($array['operator'], $array['type']);
    }

    public function isPlus(): bool
    {
        return $this->getType() === 'Plus';
    }

    public function isMinus(): bool
    {
        return $this->getType() === 'Minus';
    }
}