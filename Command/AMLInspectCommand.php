<?php

namespace App\Command;

use App\Entity\AMLInspection;
use App\Entity\CryptoDeposit;
use App\Entity\CryptoWithdrawal;
use App\Entity\DepositTransaction;
use App\Entity\FiatWithdrawal;
use App\Integration\BilderlingsService;
use App\Integration\MultinodeService;
use App\Verification\Monolith\AMLService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AMLInspectCommand extends Command
{
    protected static $defaultName = 'app:aml-inspect';

    protected $em;

    protected $amlService;

    protected $bilderlingsService;

    protected $multinodeService;

    public function __construct(AMLService $amlServie, EntityManagerInterface $em,
                                BilderlingsService $bilderlingsService, MultinodeService $multinodeService)
    {
        $this->em = $em;
        $this->amlService = $amlServie;
        $this->bilderlingsService = $bilderlingsService;
        $this->multinodeService = $multinodeService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Performs AML inspections')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $amlInspections = $this->em->getRepository(AMLInspection::class)
            ->findBy([
                'status' => AMLInspection::STATUS_SCHEDULED
            ]);

        /** @var AMLInspection $amlInspection */
        foreach ($amlInspections as $amlInspection) {
            if ($amlInspection->getCryptoWithdrawal() instanceof CryptoWithdrawal) {
                $this->multinodeService->checkForAml($amlInspection->getCryptoWithdrawal());
                $amlInspection->setStatus(AMLInspection::STATUS_ALLOWED);
            } elseif ($amlInspection->getFiatWithdrawal() instanceof FiatWithdrawal) {

            } elseif ($amlInspection->getCryptoDeposit() instanceof CryptoDeposit) {

            } elseif ($amlInspection->getDepositTransaction() instanceof DepositTransaction) {
                $this->bilderlingsService->checkForAml($amlInspection->getDepositTransaction());
                $amlInspection->setStatus(AMLInspection::STATUS_ALLOWED);
            } else {
                throw new \LogicException("No subject set for AML inspection");
            }
        }

        $this->em->flush();

        $io = new SymfonyStyle($input, $output);

        $io->success('Successfully inspected : ' . count($amlInspections));
    }
}
