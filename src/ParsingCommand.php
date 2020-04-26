<?php


namespace Z99Compiler;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Z99Compiler\Services\Parser\DefaultParser;

class ParsingCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('parser:parsing')
            ->setDescription('Parse tokens to parsing tree.')
            ->addArgument('tokens', InputArgument::REQUIRED, 'File path to tokens.')
            ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'Output file path.', 'parser-tree.txt');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $tokens = $input->getArgument('tokens');
        while (!file_exists($tokens)) {
            $output->writeln("File $tokens doesn't exist.");
            $helper = $this->getHelper('question');
            $question = new Question('Choose new path: ');
            $tokens = $helper->ask($input, $output, $question);
        }

        $outputFilePath = $input->getOption('output');
        $file = fopen($outputFilePath, 'wb');

        $parser = new DefaultParser();
        fwrite($file, $parser->parsing($tokens));

        $output->writeln('<info>Done!</info>');

        return 0;
    }
}