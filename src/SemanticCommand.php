<?php


namespace Z99Compiler;

use SemanticAnalyzer\BinaryOperator;
use SemanticAnalyzer\Constant;
use SemanticAnalyzer\Identifier;
use SemanticAnalyzer\SemanticAnalyzer;
use SemanticAnalyzer\Tree\TreeBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class SemanticCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('semantic:process')
            ->setDescription('Build RPN code, identifiers and constants table from parser tree.')
            ->addArgument('tree', InputArgument::REQUIRED, 'File path to parser tree.')
            ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'Output file path.', 'semantic.json')
        ->addOption('print', 'p', InputOption::VALUE_NONE, 'Print result to console.');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $treeFile = $input->getArgument('tree');
        while (!file_exists($treeFile)) {
            $output->writeln("File $treeFile doesn't exist.");
            $question = new Question('Choose new path: ');
            $treeFile = $this->getHelper('question')->ask($input, $output, $question);
        }

        $parserTree = json_decode(file_get_contents($treeFile), true, 512, JSON_THROW_ON_ERROR);
        $tree = TreeBuilder::fromJson($parserTree['program'], 'program');

        $semantic = new SemanticAnalyzer();

        $semantic->process($tree);

        $results['Identifiers'] = $semantic->getIdentifiers();
        $results['Constants'] = $semantic->getConstants();
        $results['RPNCode'] = $semantic->getRPNCode();

        if ($input->getOption('print')) {
            $this->printResults($semantic, $output);
        }

        $file = fopen($input->getOption('output'), 'wb');
        fwrite($file, json_encode($results, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));

        $output->writeln('<info>Done!</info>');

        return 0;
    }

    private function printResults(SemanticAnalyzer $semantic, OutputInterface $output): void
    {
        $output->writeln('<comment>Identifiers:</comment>');
        $output->writeln('Id   Name       Type       Value');
        foreach ($semantic->getIdentifiers() as $identifier) {
            $output->writeln($identifier);
        }
        $output->writeln('');

        $output->writeln('<comment>Constants:</comment>');
        echo 'Id   Value      Type' . PHP_EOL;
        foreach ($semantic->getConstants() as $constant) {
            $output->writeln($constant);
        }
        $output->writeln('');

        $output->writeln('<comment>RPN:</comment>');
        foreach ($semantic->getRPNCode() as $instruction) {
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
    }
}