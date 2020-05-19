<?php


namespace Tests\BranchStatement\SimpleTrueBranch;


use Tests\CompilerTestCase;
use Z99Compiler\Entity\Tree\Node;

class SimpleTrueBranchTest extends CompilerTestCase
{
    /**
     * { @inheritdoc }
     */
    protected $filePath = __DIR__ . '/SimpleTrueBranch.z99';

    /**
     * @depends testParser
     * @param Node $tree
     * @return array
     */
    public function testSemantic(Node $tree): array
    {
        $semantic = parent::testSemantic($tree);

        $this->assertHasIdentifier($semantic['Identifiers'], 'a', 'int');

        return $semantic;
    }
}