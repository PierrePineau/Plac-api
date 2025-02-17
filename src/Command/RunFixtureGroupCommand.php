<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'fixture:run',
    description: 'Run one fixture group',
)]
class RunFixtureGroupCommand extends Command
{
    private $container;
    public function __construct($container)
    {
        $this->container = $container;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            // the command help shown when running the command with the "--help" option
            ->setHelp('Run one fixture group')
            ->setDescription('This command allows you to automatically run one fixture group')
            ->addArgument('group', InputArgument::REQUIRED, 'The fixture group to run')
            ->addOption('yes', 'y', InputOption::VALUE_NONE, 'Skip confirmation before any BDD update')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $io = new SymfonyStyle($input, $output);

            $group = $input->getArgument('group');

            $yes = $input->getOption('yes');
            if ($yes) {
                $confirm = true;
            }else{
                $confirm = $io->confirm('Are you sure to run one fixture group ? (y/n) ', false);
            }

            if ($confirm) {
                $io->title('Run one fixture group');
                $projectDir = $this->container->getParameter('kernel.project_dir');
                $tabprocess = [
                    // 'make:migration' => [$_ENV['ALLIAS_PHP'], "{$projectDir}/bin/console", "make:migration"],
                    'd:f:l --append' => [$_ENV['ALLIAS_PHP'], "{$projectDir}/bin/console", "doctrine:fixtures:load", "--append", "--group={$group}"],
                ];
                $io->progressStart(count($tabprocess));
                foreach ($tabprocess as $key => $processInfos) {
                        $io->text('Run : ' . $key);
                        $process = new Process($processInfos);
                        $process->run();
                        if (!$process->isSuccessful()) {
                            dump($process->getErrorOutput());
                            throw new \Exception($process->getErrorOutput());
                        }
                        $io->progressAdvance();
                    }
                $io->progressFinish();
                $io->success('Fixture group run successfully');
            }

            return Command::SUCCESS;
        } catch (\Throwable $th) {
            $io->caution($th->getMessage());
            return Command::FAILURE;
        }
    }
}
