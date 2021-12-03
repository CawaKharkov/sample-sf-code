<?php

namespace App\Command;

use App\Integration\BCCService;
use App\Integration\GetIDService;
use App\Integration\MultinodeService;
use App\Integration\OrderApiService;
use App\Verification\Monolith\MonolithServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class HealthCheckCommand extends Command
{
    protected static $defaultName = 'app:health-check';

    protected $monolithService;

    protected $bccService;

    protected $getIdService;

    protected $multinodeService;

    protected $orderApiService;

    protected $em;

    public function __construct(
        MonolithServiceInterface $monolithService,
        BCCService $bccService,
        GetIDService $getIdService,
        MultinodeService $multinodeService,
        OrderApiService $orderApiService,
        EntityManagerInterface $em
    )
    {
        $this->monolithService = $monolithService;
        $this->bccService = $bccService;
        $this->getIdService = $getIdService;
        $this->multinodeService = $multinodeService;
        $this->orderApiService = $orderApiService;
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Checks availability of integration services')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->text('Start checking...');

        $this->bccService->test();
        $io->success('BCC service OK');

        if ($this->orderApiService->test()) {
            $io->success('OrderApi service OK');
        } else {
            $io->error('OrderApi error');
        }
    }
}
