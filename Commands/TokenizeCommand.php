<?php

namespace Commands;

use InvalidArgumentException;
use Symfony\Component\Console\Question\Question;
use Z99Compiler\Services\Lexer\Antlr4Lexer;
use Z99Compiler\Services\Lexer\DefaultLexer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TokenizeCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('lexer:tokenize')
            ->setDescription('Tokenize program text.')
            ->addArgument('program', InputArgument::REQUIRED, 'File path to program written on z99.')
            ->addOption('lexer', 'l', InputOption::VALUE_OPTIONAL, 'The lexer to be used (antlr4, default).', 'default')
            ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'Output file path.', 'tokens.txt');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $program = $input->getArgument('program');
        while (!file_exists($program)) {
            $output->writeln("File $program doesn't exist.");
            $helper = $this->getHelper('question');
            $question = new Question('Choose new path: ');
            $program = $helper->ask($input, $output, $question);
        }

        $outputFilePath = $input->getOption('output');
        $file = fopen($outputFilePath, 'wb');

        $lexer = $input->getOption('lexer');

        if ($lexer === 'default') {
            $grammar = require 'grammar.php';
            $lexer = new DefaultLexer($grammar);
            fwrite($file, $lexer->tokenize($program));
        } elseif ($lexer === 'antlr4') {
            $lexer = new Antlr4Lexer();
            fwrite($file, $lexer->tokenize($program));
        } else {
            throw new InvalidArgumentException('Unknown lexer ' . $lexer);
        }

        $output->writeln('<info>Done!</info>');

        return 0;
    }
}