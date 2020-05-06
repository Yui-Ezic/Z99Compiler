<?php


namespace Z99Compiler\Entity;


use JsonSerializable;

class BinaryOperator implements JsonSerializable
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
            'object' => 'BinaryOperator',
            'operator' => $this->getOperator(),
            'type' => $this->getType()
        ];
    }

    public static function fromArray(array $array): self
    {
        return new static($array['operator'], $array['type']);
    }

    public function isMultOp(): bool
    {
        return $this->getType() === 'Star' || $this->getType() === 'Slash' || $this->getType() === 'Caret';
    }

    public function isAddOp(): bool
    {
        return $this->getType() === 'Plus' || $this->getType() === 'Minus';
    }

    public function isAssignOp(): bool
    {
        return $this->getType() === 'AssignOp';
    }

    public function isRelOp(): bool
    {
        return $this->getType() === 'RelOp';
    }
}