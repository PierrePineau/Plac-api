<?php

namespace App\Command\Admin;

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
    name: 'admin:doctrine:update',
    description: 'Update the database schema',
)]
class UpdateBddCommand extends Command
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
            ->setHelp('Update the database schema')
            ->setDescription('This command allows you to automatically update the database schema')
            ->addOption('yes', 'y', InputOption::VALUE_NONE, 'Skip confirmation before any BDD update')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $io = new SymfonyStyle($input, $output);

            $yes = $input->getOption('yes');
            if ($yes) {
                $confirm = true;
            }else{
                $confirm = $io->confirm('Are you sure to update BDD ? (y/n) ', false);
            }
            
            if ($confirm) {
                $io->title('Update BDD');
                $projectDir = $this->container->getParameter('kernel.project_dir');
                $tabprocess = [
                    // 'make:migration' => [$_ENV['ALLIAS_PHP'], "{$projectDir}/bin/console", "make:migration"],
                    'doctrine:schema:update' => [$_ENV['ALLIAS_PHP'], "{$projectDir}/bin/console", "doctrine:schema:update", "--force", "--complete"],
                ];
                $io->progressStart(count($tabprocess));
                foreach ($tabprocess as $key => $processInfos) {
                        $io->text('Run : ' . $key);
                        $process = new Process($processInfos);
                        $process->run();
                        if (!$process->isSuccessful()) {
                            throw new \Exception($process->getErrorOutput());
                        }
                        $io->progressAdvance();
                    }
                $io->progressFinish();
                $io->success('BDD updated');
            }

            return Command::SUCCESS;
        } catch (\Throwable $th) {
            $io->caution($th->getMessage());
            return Command::FAILURE;
        }
    }
}
