<?php


namespace SemanticAnalyzer;


use RuntimeException;
use SemanticAnalyzer\Handlers\AssignHandler;
use SemanticAnalyzer\Handlers\ConstantsTableHandler;
use SemanticAnalyzer\Handlers\DeclareListHandler;
use SemanticAnalyzer\Tree\Node;

class SemanticAnalyzer
{
    /**
     * @var array
     */
    private $RPNCode = [];

    /**
     * @var Constant[]
     */
    private $constants = [];

    /**
     * @var Identifier[]
     */
    private $identifiers = [];

    /**
     * @var AssignHandler
     */
    private $assignHandler;

    /**
     * @var DeclareListHandler
     */
    private $declareListHandler;

    /**
     * @var ConstantsTableHandler
     */
    private $constantsTableHandler;

    public function process(Node $node): void
    {
        $this->declareListHandler = new DeclareListHandler();
        $this->identifiers = $this->buildIdentifiersTable($node);
        $this->constantsTableHandler = new ConstantsTableHandler();
        $this->constants = $this->buildConstantsTable($node);
        $this->assignHandler = new AssignHandler($this->identifiers, $this->constants);
        $this->buildRPNCode($node);
    }

    private function buildIdentifiersTable(Node $node): array
    {
        if (($node = $this->findFirst('declareList', $node)) === null) {
            throw new RuntimeException('Can not find declareList in program tree.');
        }

        $this->declareListHandler->handle($node);
        return $this->declareListHandler->getIdentifiers();
    }

    private function buildConstantsTable(Node $node): array
    {
        if (($node = $this->findFirst('statementList', $node)) === null) {
            throw new RuntimeException('Can not find statementList in program tree.');
        }

        $this->constantsTableHandler->handle($node);
        return $this->constantsTableHandler->getConstants();
    }

    private function buildRPNCode(Node $node): void
    {
        if ($node->getName() === 'assign') {
            $this->RPNCode[] = $this->assignHandler->handle($node);
        } elseif ($children = $node->getChildren()) {
            foreach ($children as $child) {
                $this->buildRPNCode($child);
            }
        }
    }

    private function findFirst($name, Node $node): ?Node
    {
        if ($node->getName() === $name) {
            return $node;
        }

        if ($children = $node->getChildren()) {
            foreach ($children as $child) {
                if ($this->findFirst($name, $child)) {
                    return $child;
                }
            }
        }

        return null;
    }

    /**
     * @return Identifier[]
     */
    public function getIdentifiers(): array
    {
        return $this->identifiers;
    }

    /**
     * @return Constant[]
     */
    public function getConstants(): array
    {
        return $this->constants;
    }

    /**
     * @return array
     */
    public function getRPNCode(): array
    {
        return $this->RPNCode;
    }
}