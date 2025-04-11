<?php

namespace Phiki\Console;

use Phiki\Phiki;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('highlight', 'Highlight the given file using the chosen theme and grammar.')]
class HighlightCommand extends Command
{
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $phiki = new Phiki;

        echo $phiki->codeToTerminal(
            file_get_contents($input->getArgument('file')),
            $input->getOption('grammar'),
            $input->getOption('theme'),
        );

        return 1;
    }

    protected function configure()
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'The file to highlight.')
            ->addOption('theme', 't', InputOption::VALUE_REQUIRED, 'The theme to use.')
            ->addOption('grammar', 'g', InputOption::VALUE_REQUIRED, 'The grammar to use.');
    }
}
