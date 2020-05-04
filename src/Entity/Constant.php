<?php


namespace Z99Compiler\Entity;


use JsonSerializable;

class Constant implements JsonSerializable
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var number
     */
    private $value;

    /**
     * @var string
     */
    private $type;

    public function __construct(int $id, $value, string $type)
    {
        $this->id = $id;
        $this->value = $value;
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * @return number
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        return sprintf('@%02d  %-10s %-10s',
            $this->getId(),
            (string)$this->getValue(),
            $this->getType()
        );
    }

    public function jsonSerialize()
    {
        return [
            'object' => 'Constant',
            'id' => $this->getId(),
            'value' => $this->getValue(),
            'type' => $this->getType()
        ];
    }

    public static function fromArray(array $array): self
    {
        return new static($array['id'], $array['value'], $array['type']);
    }
}