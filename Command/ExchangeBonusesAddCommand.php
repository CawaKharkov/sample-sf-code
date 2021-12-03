<?php

namespace App\Command;

use App\Entity\Currency;
use App\Entity\ExchangeBonus;
use App\Entity\User;
use App\Entity\UserAccount;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ExchangeBonusesAddCommand extends Command
{
    protected static $defaultName = 'exchange:bonuses-add';

    protected $em;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Add bonuses to users accounts')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $currency = $this->em->getRepository(Currency::class)
            ->findOneByCode('USDT');

        $users = $this->em->getRepository(User::class)
            ->findAll();

        $i = 0;
        /** @var User $user */
        foreach ($users as $user) {
            /** @var UserAccount $userAccount */
            $userAccount = $user->getPrimaryAccountByCurrency($currency);
            $amount = rand(0, 9) / 10;
            $bonus = (new ExchangeBonus())
                ->setAccount($userAccount)
                ->setAmount($amount);

            $this->em->persist($bonus);
            $i++;

            $this->logger->info("Added {$amount} bonuses to USDT primary {$user->getEmail()} account");
        }

        $this->em->flush();

        $this->logger->info("Successfully added $i bonuses");

        $io->success("Successfully added $i bonuses");
    }
}
