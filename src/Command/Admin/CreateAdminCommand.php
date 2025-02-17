<?php

namespace App\Command\Admin;

use App\Service\Admin\AdminManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'admin:user:create',
    description: 'Create a new administrator',
)]
class CreateAdminCommand extends Command
{
    private $entityManager;
    private $repoAdmin;
    private $container;
    private $adminManager;
    public function __construct($container)
    {
        parent::__construct();
        $this->container = $container;
        $this->adminManager = $this->container->get(AdminManager::class);
    }

    protected function configure(): void
    {
        $this
            // the command help shown when running the command with the "--help" option
            ->setHelp('This command allows you to create a new administrator')
            ->setDescription('Create a new administrator')
            ->addArgument(
                'email',
                InputArgument::OPTIONAL,
                'Email :'
            )
            ->addArgument(
                'password',
                InputArgument::OPTIONAL,
                'Password :'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $io = new SymfonyStyle($input, $output);

            $io->title('Create a new administrator');

            if ($input->getArgument('email') == null) {
                $email = $io->ask('Email ');
            }else{
                $email = $input->getArgument('email');
            }
            $validateEmail = filter_var($email, FILTER_VALIDATE_EMAIL);
            if ((!isset($email) || !$validateEmail)) {
                $io->caution('Email invalide');
                return Command::INVALID;
            }

            if ($input->getArgument('password') == null) {
                $password = $io->ask('Password ');
            }else{
                $password = $input->getArgument('password');
            }
            if (!isset($password)) {
                $io->caution('Mot de passe invalide');
                return Command::INVALID;
            }
            $response = $this->adminManager->create([
                'email' => $email,
                'password' => $password
            ]);
            if ($response['success']) {
                $io->success($response['message']);
                return Command::SUCCESS;
            }else{
                $io->caution($response['message']);
                return Command::INVALID;
            }
        } catch (\Throwable $th) {
            $io->caution($th->getMessage());
            return Command::FAILURE;
        }
    }
}
