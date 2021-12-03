<?php

namespace App\Controller;

use App\Entity\Currency;
use App\Entity\Model\Info\DepositInfo;
use App\Entity\User;
use App\Entity\UserDepositMethod;
use App\Repository\CurrencyRepository;
use App\Repository\UserDepositCryptoaddressRepository;
use App\Repository\UserDepositMethodRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


/**
 * @Rest\Route("/api/v1/info")
 */
class InfoController extends AbstractFOSRestController
{
    /**
     * @Rest\Route("/deposit", name="api_info_deposit", methods={"GET"})
     * @Rest\View()
     *
     * @QueryParam(name="currency", description="The fiat currency code", nullable=false, strict=true)
     *
     * @SWG\Response(response=200, description="Deposit account information",
     *     @SWG\Schema(type="array", @SWG\Items(ref=@Model(type=App\Entity\Model\Info\DepositInfo::class))))
     * )
     */
    public function deposit(ParamFetcher $paramFetcher, CurrencyRepository $currencyRepository,
                            UserDepositMethodRepository $depositMethodRepository)
    {
        $currency = $currencyRepository->findOneBy([
            'code' => $paramFetcher->get('currency'),
            'type' => Currency::TYPE_FIAT
        ]);

        if (!$currency) {
            throw new BadRequestHttpException("Invalid fiat currency code: {$paramFetcher->get('currency')}");
        }

        /** @var User $user */
        $user = $this->getUser();

        $depositMethod = $depositMethodRepository->findOneBy([
            'user' => $user,
            'currency' => $currency
        ]);

        if (!$depositMethod) {
            throw new BadRequestHttpException("No deposit method found for currency {$currency->getCode()}");
        }

        $info1 = new DepositInfo();
        $info1->depositMethod = 'Fidor Bank AG (Sepa)';
        $info1->accountName = 'Kraken, Paywald Ltd.';
        $info1->address = 'One London wall, London, EC2Y 5EB, United Kingdom';
        $info1->iban = 'DE31 7002 2200 0071 7885 12';
        $info1->bankName = 'Fidor Bank AG';
        $info1->bic = 'FDDODEMMXXX';
        $info1->bankAddress = 'Sandstrasse 33, D-80335 Munchen, Germany';
        $info1->reference = $depositMethod->getReference();

        $info2 = new DepositInfo();
        $info2->depositMethod = 'Fidor Bank AG (Sepa) 2';
        $info2->accountName = 'Kraken, Paywald Ltd. 2';
        $info2->address = 'One London wall, London, EC2Y 5EB, United Kingdom 2';
        $info2->iban = 'DE31 7002 2200 0071 7885 12 2';
        $info2->bankName = 'Fidor Bank AG 2';
        $info2->bic = 'FDDODEMMXXX 2';
        $info2->bankAddress = 'Sandstrasse 33, D-80335 Munchen, Germany 2';
        $info2->reference = $depositMethod->getReference();

        return [$info1, $info2];
    }

}
