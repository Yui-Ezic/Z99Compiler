<?php


namespace Z99Compiler\Tables;


use JsonSerializable;
use Z99Compiler\Entity\Constant;

class ConstantsTable implements JsonSerializable
{
    /**
     * @var Constant[]
     */
    private $constants = [];

    /**
     * @var int
     */
    private $id = 0;

    /**
     * @param $value
     * @param $type
     * @return Constant
     */
    public function addConstant($value, $type): Constant
    {
        if (($constant = $this->find($value)) !== null) {
            return $constant;
        }

        $constant = $this->add($value, $type);

        return $constant;
    }

    /**
     * @param $value
     * @return Constant|null
     */
    public function find($value): ?Constant
    {
        foreach ($this->constants as $constant) {
            if ($constant->getValue() === (string)$value) {
                return $constant;
            }
        }

        return null;
    }

    /**
     * @param $value
     * @param $type
     * @return Constant
     */
    private function add($value, $type): Constant
    {
        $id = $this->id++;
        $constant = new Constant($id, $value, $type);
        $this->constants[$id] = $constant;
        return $constant;
    }

    /**
     * @return Constant[]
     */
    public function getConstants(): array
    {
        return $this->constants;
    }

    /**
     * @return mixed|Constant[]
     */
    public function jsonSerialize()
    {
        return $this->constants;
    }
}