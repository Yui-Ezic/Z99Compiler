<?php


namespace Z99Compiler\Entity;


use JsonSerializable;

class JumpIf implements JsonSerializable
{
    /**
     * @var int|null
     */
    private $address;

    public function __construct(?int $address = null)
    {
        $this->address = $address;
    }

    public function setAddress(int $address): void
    {
        $this->address = $address;
    }

    /**
     * @return int
     */
    public function getAddress(): int
    {
        return $this->address;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'object' => 'JumpIf',
            'address' => $this->getAddress()
        ];
    }
}