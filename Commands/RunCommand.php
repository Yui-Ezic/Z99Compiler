<?php


namespace Commands;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Z99Compiler\Entity\BinaryOperator;
use Z99Compiler\Entity\Constant;
use Z99Compiler\Entity\Identifier;
use Z99Compiler\Services\Interpreter\DefaultInterpreter;
use Z99Compiler\Services\Lexer\DefaultLexer;
use Z99Compiler\Services\Parser\DefaultParser;
use Z99Compiler\Services\SemanticAnalyzer\DefaultSemanticAnalyzer;
use Z99Compiler\Tables\ConstantsTable;
use Z99Compiler\Tables\IdentifierTable;

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
        $results = $this->interpreter->process($semantic['RPNCode'], $semantic['Constants'], $semantic['Identifiers']);

        $this->printResults($output, $results['Identifiers'], $results['Constants'], $semantic['RPNCode']);

        return 0;
    }

    private function printResults(OutputInterface $output, IdentifierTable $identifiers, ConstantsTable $constants, $rpnCode): void
    {
        $output->writeln('<comment>RPN:</comment>');
        foreach ($rpnCode as $instruction) {
            foreach ($instruction as $item) {
                if ($item instanceof Constant) {
                    $output->write('(' . $item->getType() . ' : ' . $item->getValue() . ') ');
                } elseif ($item instanceof Identifier) {
                    $output->write('(' . $item->getType() . ' : ' . $item->getName() . ') ');
                } elseif ($item instanceof BinaryOperator) {
                    $output->write('(' . $item->getType() . ' : ' . $item->getOperator() . ') ');
                } else {
                    $output->write('<error>Undefined element</error> ');
                }
            }
            $output->writeln('');
        }
        $output->writeln('');

        $output->writeln('<comment>Identifiers:</comment>');
        $output->writeln('Id   Name       Type       Value');
        foreach ($identifiers->getIdentifiers() as $identifier) {
            $output->writeln($identifier);
        }
        $output->writeln('');

        $output->writeln('<comment>Constants:</comment>');
        echo 'Id   Value      Type' . PHP_EOL;
        foreach ($constants->getConstants() as $constant) {
            $output->writeln($constant);
        }
    }
}