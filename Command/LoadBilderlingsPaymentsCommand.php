<?php

namespace App\Command;

use App\Entity\SmsCode;
use App\Entity\User;
use App\Integration\BilderlingsService;
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

class LoadBilderlingsPaymentsCommand extends Command
{
    protected static $defaultName = 'app:load-bilderlings-payments';

    protected $monolithService;

    protected $bilderlingsService;

    protected $em;

    protected $uasTokenStorage;

    protected $smsService;

    protected $uasService;

    public function __construct(MonolithServiceInterface $monolithService, EntityManagerInterface $em, UASTokenStorageInterface $uasTokenStorage,
        SmsServiceInterface $smsService, UASService $uasService, BilderlingsService $bilderlingsService)
    {
        $this->monolithService = $monolithService;
        $this->em = $em;
        $this->uasTokenStorage = $uasTokenStorage;
        $this->smsService = $smsService;
        $this->uasService = $uasService;
        $this->bilderlingsService = $bilderlingsService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Loads payments from Bilderlings')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        if ($input->getOption('option1')) {
            // ...
        }

        $this->bilderlingsService->loadPayments();


        $io->success('Payments successfully loaded from Bilderlings');
    }
}
