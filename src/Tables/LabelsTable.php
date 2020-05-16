<?php


namespace Z99Compiler\Tables;


use JsonSerializable;
use RuntimeException;
use Z99Compiler\Entity\Label;

class LabelsTable implements JsonSerializable
{
    /**
     * @var array
     */
    private $labels = [];

    /**
     * Add label to table
     * @param Label $label
     * @param int $address
     */
    public function add(Label $label, int $address): void
    {
        if ($this->getAddress($label) !== null) {
            throw new RuntimeException('Label ' . $label->getName() . ' already exist in table.');
        }
        $this->labels[$label->getName()] = $address;
    }

    /**
     * Get address of label or null
     * @param Label $label
     * @return int|null
     */
    public function getAddress(Label $label): ?int
    {
        foreach ($this->labels as $name => $address) {
            if ($name === $label->getName()) {
                return $address;
            }
        }

        return null;
    }

    /**
     * Get address of label or fail
     * @param Label $label
     * @return int
     */
    public function getAddressOrFail(Label $label): int
    {
        if (($address = $this->getAddress($label)) !== null) {
            return $address;
        }

        throw new RuntimeException('Cannot find label ' . $label->getName() . ' in labels table.');
    }

    public function getLabels(): array
    {
        return $this->labels;
    }

    public function jsonSerialize()
    {
        return $this->labels;
    }

    public static function fromArray(array $array): self
    {
        $labels = new static();
        foreach ($array as $key => $item) {
            $label = new Label($key);
            $labels->add($label, $item);
        }

        return $labels;
    }
}