<?php


namespace Commands;

use Z99Compiler\Entity\BinaryOperator;
use Z99Compiler\Entity\Constant;
use Z99Compiler\Entity\Identifier;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Z99Compiler\Entity\UnaryOperator;
use Z99Compiler\Services\SemanticAnalyzer\DefaultSemanticAnalyzer;
use Z99Compiler\Tables\ConstantsTable;
use Z99Compiler\Tables\IdentifiersTable;

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

        $semantic = new DefaultSemanticAnalyzer();
        $results = $semantic->processFile($treeFile);

        $file = fopen($input->getOption('output'), 'wb');
        fwrite($file, json_encode($results, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));

        if ($input->getOption('print')) {
            $this->printResults($output, $results['Identifiers'], $results['Constants'], $results['RPNCode']);
        } else {
            $output->writeln('<info>Done!</info>');
        }

        return 0;
    }

    private function printResults(OutputInterface $output, IdentifiersTable $identifiers, ConstantsTable $constants, $rpnCode): void
    {
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
        $output->writeln('');

        $output->writeln('<comment>RPN:</comment>');
        foreach ($rpnCode as $instruction) {
            if ($instruction instanceof Constant) {
                $output->write('(' . $instruction->getType() . ' : ' . $instruction->getValue() . ') ');
            } elseif ($instruction instanceof Identifier) {
                $output->write('(' . $instruction->getType() . ' : ' . $instruction->getName() . ') ');
            } elseif ($instruction instanceof BinaryOperator) {
                $output->write('(' . $instruction->getType() . ' : ' . $instruction->getOperator() . ') ');
            } elseif ($instruction instanceof UnaryOperator) {
                $output->write('unary(' . $instruction->getType() . ' : ' . $instruction->getOperator() . ') ');
            } else {
                $output->write('<error>Undefined element</error> ');
            }
        }
    }
}