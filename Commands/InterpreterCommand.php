<?php


namespace Commands;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Z99Compiler\Services\Interpreter\DefaultInterpreter;

class InterpreterCommand extends Command
{
    /**
     * @var DefaultInterpreter
     */
    private $interpreter;

    public function __construct(DefaultInterpreter $interpreter)
    {
        parent::__construct(null);
        $this->interpreter = $interpreter;
    }

    protected function configure(): void
    {
        $this->setName('interpreter:process')
            ->setDescription('Process RPN code.')
            ->addArgument('semantic', InputArgument::REQUIRED, 'File path to semantic analyzer output.');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $semanticFile = $input->getArgument('semantic');
        while (!file_exists($semanticFile)) {
            $output->writeln("File $semanticFile doesn't exist.");
            $question = new Question('Choose new path: ');
            $semanticFile = $this->getHelper('question')->ask($input, $output, $question);
        }

        $this->interpreter->processFile($semanticFile);

        return 0;
    }
}