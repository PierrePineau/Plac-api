<?php

namespace App\Command\Test;

use App\Core\Utils\Messenger;
use App\Event\Client\UserCreateEvent;
use App\Event\Organisation\OrganisationCreateEvent;
use App\Service\Organisation\OrganisationManager;
use App\Service\User\UserManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'test:event',
    description: 'Try to send event, for test',
)]
class EventTestCommand extends Command
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

        $io->title('Test event');
        // $emailReceiver = $input->getArgument('email');
        $org = $io->ask('Organisation demo', '0194f783-58bc-7f20-a76a-6a826fe51bd1');
        try {
            $io->text('Send event create on ' . $org);
            $orgManager = $this->container->get(OrganisationManager::class);
            $messenger = $this->container->get(Messenger::class);
            $org = $orgManager->findOneBy([
                'uuid' => $org,
            ]);
            if (!$org) {
                throw new \Exception('org not found');
            }
            $events = [
                new OrganisationCreateEvent([
                    'organisation' => $org,
                ]),
            ];

            foreach ($events as $event) {
                $io->text('Dispatch event ' . get_class($event));
                
                $messenger->dispatchEvent($event);

                $io->text('Subscribers : ' . $event->getSubscribers(true) );
                $io->text('Errors : ' . $event->getErrors(true) );
            }
            $io->success('OK');

            return Command::SUCCESS;
        } catch (\Throwable $th) {
            throw $th;

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
