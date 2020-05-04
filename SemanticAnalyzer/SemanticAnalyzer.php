<?php


namespace SemanticAnalyzer;


use RuntimeException;
use SemanticAnalyzer\Handlers\AssignHandler;
use SemanticAnalyzer\Handlers\ConstantsTableHandler;
use SemanticAnalyzer\Handlers\DeclareListHandler;
use Z99Compiler\Entity\Tree\Node;
use Z99Compiler\Entity\Tree\Tree;
use Z99Compiler\Tables\ConstantsTable;
use Z99Compiler\Tables\IdentifierTable;

class SemanticAnalyzer
{
    /**
     * @var array
     */
    private $RPNCode = [];

    /**
     * @var ConstantsTable
     */
    private $constants = [];

    /**
     * @var IdentifierTable
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

    private function buildIdentifiersTable(Node $node): IdentifierTable
    {
        if (($node = Tree::findFirst('declareList', $node)) === null) {
            throw new RuntimeException('Can not find declareList in program tree.');
        }

        $this->declareListHandler->handle($node);
        return $this->declareListHandler->getIdentifiers();
    }

    private function buildConstantsTable(Node $node): ConstantsTable
    {
        if (($node = Tree::findFirst('statementList', $node)) === null) {
            throw new RuntimeException('Can not find statementList in program tree.');
        }

        $this->constantsTableHandler->handle($node);
        return $this->constantsTableHandler->getConstants();
    }

    private function buildRPNCode(Node $node): void
    {
        $nodes = Tree::findAll('assign', $node);
        foreach ($nodes as $item) {
            $this->RPNCode[] = $this->assignHandler->handle($item);
        }
    }

    /**
     * @return IdentifierTable
     */
    public function getIdentifiers(): IdentifierTable
    {
        return $this->identifiers;
    }

    /**
     * @return ConstantsTable
     */
    public function getConstants(): ConstantsTable
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