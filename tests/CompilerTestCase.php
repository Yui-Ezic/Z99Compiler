<?php


namespace Tests;


use PHPUnit\Framework\TestCase;
use SemanticAnalyzer\SemanticAnalyzer;
use Z99Compiler\Entity\Identifier;
use Z99Compiler\Entity\Tree\Node;
use Z99Compiler\Tables\IdentifiersTable;
use Z99Lexer\Lexer;
use Z99Lexer\Stream\FileStream;
use Z99Parser\Parser;
use Z99Parser\Streams\ArrayStream;

class CompilerTestCase extends TestCase
{
    /**
     * Path to z99 program file to be tested
     * @var string
     */
    protected $filePath;

    public function testLexer(): array
    {
        $grammar = require 'grammar.php';
        $stream = new FileStream($this->filePath);
        $lexer = new Lexer($stream, $grammar);
        $lexer->tokenize();
        $tokens = $lexer->getTokens();
        $this->assertNotEmpty($tokens);
        return $tokens;
    }

    /**
     * @depends testLexer
     * @param array $tokens
     * @return Node
     */
    public function testParser(array $tokens): Node
    {
        $stream = new ArrayStream($tokens);
        $parser = new Parser($stream);
        $tree = $parser->program();
        $this->assertSame($tree->getName(), 'program');
        return $tree;
    }

    /**
     * @depends testParser
     * @param Node $tree
     * @return array
     */
    public function testSemantic(Node $tree): array
    {
        $semantic = new SemanticAnalyzer();
        $semantic->process($tree);

        return [
            'Identifiers' => $semantic->getIdentifiers(),
            'Constants' => $semantic->getConstants(),
            'RPNCode' => $semantic->getRPNCode(),
            'Labels' => $semantic->getLabels()
        ];
    }

    /**
     * @param IdentifiersTable $identifiers
     * @param $name
     * @param $type
     */
    protected function assertHasIdentifier(IdentifiersTable $identifiers, $name, $type): void
    {
        $this->assertContains([$name => $type], array_map(static function(Identifier $identifier) {
            return [$identifier->getName() => $identifier->getType()];
        }, $identifiers->getIdentifiers()));
    }
}