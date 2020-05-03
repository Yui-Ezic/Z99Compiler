<?php


namespace Z99Compiler\Services\SemanticAnalyzer;


use JsonException;
use Z99Compiler\Entity\Tree\Node;
use SemanticAnalyzer\SemanticAnalyzer;
use Z99Compiler\Entity\Tree\TreeBuilder;

class DefaultSemanticAnalyzer
{
    /**
     * @param $fileName
     * @return array
     * @throws JsonException
     */
    public function processFile($fileName): array
    {
        $parserTree = json_decode(file_get_contents($fileName), true, 512, JSON_THROW_ON_ERROR);
        $tree = TreeBuilder::fromJson($parserTree);

        return $this->process($tree);
    }

    /**
     * @param Node $tree
     * @return array
     */
    public function process(Node $tree): array
    {
        $semantic = new SemanticAnalyzer();

        $semantic->process($tree);

        $results['Identifiers'] = $semantic->getIdentifiers();
        $results['Constants'] = $semantic->getConstants();
        $results['RPNCode'] = $semantic->getRPNCode();

        return $results;
    }
}