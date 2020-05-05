<?php


namespace Z99Compiler\Tables;


use JsonSerializable;
use RuntimeException;
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
            if ((string)$constant->getValue() === (string)$value) {
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

    /**
     * @param Constant $constant
     */
    private function setConstant(Constant $constant)
    {
        $this->constants[$constant->getId()] = $constant;
    }

    /**
     * @param array $array
     * @return static
     */
    public static function fromArray(array $array): self
    {
        $constantTable = new static();
        foreach ($array as $item) {
            if ($item['object'] !== 'Constant') {
                throw new RuntimeException('Unexpected object ' . $item['object'] . ' instead of constant');
            }

            $constantTable->setConstant(Constant::fromArray($item));
        }

        return $constantTable;
    }
}