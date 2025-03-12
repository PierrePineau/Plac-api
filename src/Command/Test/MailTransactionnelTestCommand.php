<?php

namespace App\Command\Test;

use App\Core\Utils\Messenger;
use App\Event\User\UserCreateEvent;
use App\Service\User\UserManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'test:mail',
    description: 'Try to send a transactionnal email',
)]
class MailTransactionnelTestCommand extends Command
{
    private $container;
    public function __construct($container)
    {
        parent::__construct();
        $this->container = $container;
    }

    protected function configure(): void
    {
        $this
            // the command help shown when running the command with the "--help" option
            // ->addOption('yes', 'y', InputOption::VALUE_NONE, 'Skip confirmation')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Test mail Transactionnel');
        // $emailReceiver = $input->getArgument('email');
        $emailReceiver = $io->ask('Email receiver', 'pierrepineau.pro@gmail.com');
        try {
            $io->text('Send emails to ' . $emailReceiver);
            $userManager = $this->container->get(UserManager::class);
            $messenger = $this->container->get(Messenger::class);
            $user = $userManager->findOneBy([
                'email' => $emailReceiver,
            ]);
            if (!$user) {
                throw new \Exception('user not found');
            }
            $events = [
                new UserCreateEvent([
                    'user' => $user,
                ]),
            ];

            foreach ($events as $event) {
                $io->text('Dispatch event ' . get_class($event));
                $messenger->dispatchEvent($event);
            }

            $io->success('Emails sent');

            return Command::SUCCESS;
        } catch (\Throwable $th) {
            throw $th;

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
