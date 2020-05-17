<?php


namespace Z99Compiler\Tables;


use JsonSerializable;
use RuntimeException;
use Z99Compiler\Entity\Constant;
use Z99Compiler\Entity\Identifier;

class IdentifiersTable implements JsonSerializable
{
    /**
     * @var Identifier[]
     */
    private $identifiers = [];

    /**
     * @var int
     */
    private $id = 0;

    /**
     * @param $name
     * @param null $type
     * @param null $value
     * @return Identifier
     */
    public function addIdentifier($name, $type = null, $value = null): Identifier
    {
        if (($identifier = $this->find($name, $type)) !== null) {
            $name = $identifier->getName();
            throw new RuntimeException("Identifier $name already exist in table.");
        }

        $identifier = $this->add($name, $type, $value);

        return $identifier;
    }

    /**
     * @param $name
     * @param $type
     * @return Identifier
     */
    private function find($name, $type): ?Identifier
    {
        if (($identifier = $this->findByName($name)) !== null) {
            $identifierType = $identifier->getType();
            if ($identifierType !== null && $identifierType !== $type) {
                throw new RuntimeException("Identifier $name already exist in table and have " . $identifierType . " type (instead of $type).");
            }

            return $identifier;
        }

        return null;
    }

    /**
     * @param $name
     * @return Identifier|null
     */
    public function findByName($name): ?Identifier
    {
        foreach ($this->identifiers as $identifier) {
            if ($identifier->getName() === $name) {
                return $identifier;
            }
        }

        return null;
    }

    /**
     * @param $name
     * @param null $type
     * @param null $value
     * @return Identifier
     */
    private function add($name, $type = null, $value = null): Identifier
    {
        $id = $this->id++;
        $identifier = new Identifier($id, $name, $type, $value);
        $this->identifiers[$id] = $identifier;
        return $identifier;
    }

    /**
     * Adds identifier to table without repetition.
     *
     * @param $name
     * @param $type
     * @param $value
     * @return Identifier
     */
    public function addIdentifierIfNotExist($name, $type = null, $value = null): Identifier
    {
        if (($identifier = $this->find($name, $type)) !== null) {
            return $identifier;
        }

        $identifier = $this->add($name, $type, $value);

        return $identifier;
    }

    /**
     * @return Identifier[]
     */
    public function getIdentifiers(): array
    {
        return $this->identifiers;
    }

    /**
     * Change value of identifier by his id
     * @param $id
     * @param Constant|Identifier $value
     */
    public function changeValue($id, $value): void
    {
        $identifier = $this->identifiers[$id];

        // type assignment rules
        $typesMap = [
            'int' => ['int'],
            'real' => ['int', 'real'],
            'bool' => ['bool']
        ];

        if (!array_key_exists($identifier->getType(), $typesMap)) {
            throw new RuntimeException('Invalid identifier type ' . $identifier->getType());
        }

        if (!in_array($value->getType(), $typesMap[$identifier->getType()], true)) {
            throw new RuntimeException('Cannot set variable (' . $identifier->getName() . ' : ' . $identifier->getType() . ') to ' . $value->getType());
        }

        $identifier->setValue($value->getValue());
    }

    public function jsonSerialize()
    {
        return $this->identifiers;
    }

    /**
     * @param Identifier $identifier
     */
    private function setIdentifier(Identifier $identifier): void
    {
        $this->identifiers[$identifier->getId()] = $identifier;
    }

    /**
     * @param array $array
     * @return static
     */
    public static function fromArray(array $array): self
    {
        $identTable = new static();
        foreach ($array as $item) {
            if ($item['object'] !== 'Identifier') {
                throw new RuntimeException('Unexpected object ' . $item['object'] . ' instead of identifier');
            }

            $identTable->setIdentifier(Identifier::fromArray($item));
        }

        return $identTable;
    }
}