<?php


namespace Commands;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Z99Compiler\Services\Interpreter\DefaultInterpreter;
use Z99Compiler\Services\Lexer\DefaultLexer;
use Z99Compiler\Services\Parser\DefaultParser;
use Z99Compiler\Services\SemanticAnalyzer\DefaultSemanticAnalyzer;

class RunCommand extends Command
{
    /**
     * @var DefaultLexer
     */
    private $lexer;

    /**
     * @var DefaultParser
     */
    private $parser;

    /**
     * @var DefaultSemanticAnalyzer
     */
    private $semanticAnalyzer;

    /**
     * @var DefaultInterpreter
     */
    private $interpreter;

    public function __construct(DefaultLexer $lexer, DefaultParser $parser, DefaultSemanticAnalyzer $semanticAnalyzer, DefaultInterpreter $interpreter)
    {
        parent::__construct(null);
        $this->lexer = $lexer;
        $this->parser = $parser;
        $this->semanticAnalyzer = $semanticAnalyzer;
        $this->interpreter = $interpreter;
    }

    protected function configure(): void
    {
        $this->setName('run')
            ->setDescription('Interpret Z99 program.')
            ->addArgument('program', InputArgument::REQUIRED, 'Path to program source file.');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $file = $input->getArgument('program');
        while (!file_exists($file)) {
            $output->writeln("File $file doesn't exist.");
            $question = new Question('Choose new path: ');
            $file = $this->getHelper('question')->ask($input, $output, $question);
        }

        $tokens = $this->lexer->tokenize($file);
        $tree = $this->parser->parsingTokenArray($tokens);
        $semantic = $this->semanticAnalyzer->process($tree);
        $this->interpreter->process($semantic['RPNCode'], $semantic['Constants'], $semantic['Identifiers']);

        return 0;
    }
}