<?php

namespace App\Command;

use App\Entity\ReferralEvent;
use App\Entity\User;
use App\Integration\IntegrationException;
use App\Verification\Monolith\MonolithServiceInterface;
use App\Verification\SmsServiceInterface;
use App\Verification\UAS\UASTokenStorageInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Ronte\UASAuthenticationBundle\UAS\UASService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CheckUsersStatusesCommand extends Command
{
    protected static $defaultName = 'app:check-users-statuses';

    protected $monolithService;

    protected $em;

    protected $uasTokenStorage;

    protected $smsService;

    protected $uasService;

    protected $logger;

    public function __construct(MonolithServiceInterface $monolithService, EntityManagerInterface $em, UASTokenStorageInterface $uasTokenStorage,
        SmsServiceInterface $smsService, UASService $uasService, LoggerInterface $logger)
    {
        $this->monolithService = $monolithService;
        $this->em = $em;
        $this->uasTokenStorage = $uasTokenStorage;
        $this->smsService = $smsService;
        $this->uasService = $uasService;
        $this->logger = $logger;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription("Checks users' registration statuses in Monolith")
            /*->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')*/
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        /** @var User[] $users */
        $users = $this->em->getRepository(User::class)
            ->findActiveOnUAS();

        foreach ($users as $user) {
            // prolong token
            $this->uasService->introspect($user->getUasToken());
            $monolithStatusText = $this->monolithService->getUserStatus($user);
            $monolithStatus = array_search($monolithStatusText, User::$monolithStatusesText);
            if (!$monolithStatus) {
                throw new IntegrationException("Unknown Monolith user status: " . $monolithStatusText);
            }
            if ($user->getMonolithStatus() != $monolithStatus) {
                $user->setMonolithStatus($monolithStatus);

                if ($monolithStatus == User::STATUS_MONOLITH_REGISTERED) {
                    $event = (new ReferralEvent())
                        ->setEvent(ReferralEvent::TYPE_VERIFICATION_SUCCESS)
                        ->setEmail($user->getEmail())
                        ->setEventDate(new \DateTimeImmutable())
                        ->setEventData('{"data":{"hash":"'.$user->getReferralPasswordHash().'"}}')
                        ->setAccepted(false);
                    $this->em->persist($event);
                } elseif ($monolithStatus == User::STATUS_MONOLITH_REJECTED) {
                    $event = (new ReferralEvent())
                        ->setEvent(ReferralEvent::TYPE_VERIFICATION_FAILED)
                        ->setEmail($user->getEmail())
                        ->setEventDate(new \DateTimeImmutable())
                        ->setEventData('{"data":{"reason":"User verification failed: application rejected"}}')
                        ->setAccepted(false);
                    $this->em->persist($event);
                }

                $message = "Set user {$user->getEmail()} Monolith status to $monolithStatusText ($monolithStatus)";
                $io->success($message);
                $this->logger->info($message);
            }
        }

        $this->em->flush();

    }
}
