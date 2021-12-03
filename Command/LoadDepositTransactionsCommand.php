<?php

namespace App\Command;

use App\Entity\SmsCode;
use App\Entity\User;
use App\Verification\Monolith\MonolithServiceInterface;
use App\Verification\SmsServiceInterface;
use App\Verification\UAS\UASTokenStorageInterface;
use Doctrine\ORM\EntityManagerInterface;
use Ronte\UASAuthenticationBundle\UAS\UASService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class LoadDepositTransactionsCommand extends Command
{
    protected static $defaultName = 'app:load-deposit-transactions';

    protected $monolithService;

    protected $em;

    protected $uasTokenStorage;

    protected $smsService;

    protected $uasService;

    public function __construct(MonolithServiceInterface $monolithService, EntityManagerInterface $em, UASTokenStorageInterface $uasTokenStorage,
        SmsServiceInterface $smsService, UASService $uasService)
    {
        $this->monolithService = $monolithService;
        $this->em = $em;
        $this->uasTokenStorage = $uasTokenStorage;
        $this->smsService = $smsService;
        $this->uasService = $uasService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Loads new deposit transactions from Monolith')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $phone = '+7 (916) 916-06-21';

        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        if ($input->getOption('option1')) {
            // ...
        }

        /** @var User $user */
        $user = $this->em->getRepository(User::class)
            ->findOneByPhone($phone);

        if (!$user) {
            throw new \Exception("User with phone $phone not found");
        }

        if (!$this->uasTokenStorage->hasToken($user)) {
            // send code:
            $smsCode = (new SmsCode())
                ->setUser($user)
                ->setPhone($user->getPhone())
            ;
            $this->smsService->sendCode($smsCode);

            // check code:
            /** @var QuestionHelper $dialog */
            $dialog = $this->getHelper('question');
            $question = new Question("Enter the sms code sent to $phone: ");
            $code = $dialog->ask($input, $output, $question);

            $smsCode = (new SmsCode())
                ->setUser($user)
                ->setPhone($user->getPhone())
                ->setCode($code)
            ;
            if ($this->smsService->checkCode($smsCode)) {
                print 'Code OK';
            } else {
                throw new \Exception("Invalid SMS code entered");
            }
        }

        // prolong token
        $this->uasService->introspect($user->getUasToken());

        $this->monolithService->loadDepositTransactions($user);

        $io->success('Deposit transactions successfully loaded from Monolith');
    }
}
