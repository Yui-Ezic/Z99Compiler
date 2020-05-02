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
}