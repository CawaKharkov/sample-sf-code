<?php

namespace App\Controller;

use App\Entity\CryptoDeposit;
use App\Entity\CryptoWithdrawal;
use App\Entity\Currency;
use App\Entity\MultinodeRequestCreate;
use App\Entity\MultinodeRequestCreateItem;
use App\Entity\MultinodeRequestUpdate;
use App\Entity\MultinodeRequestUpdateItem;
use App\Entity\MultinodeRequestUpdateItemResults;
use App\Entity\Transaction;
use App\Entity\User;
use App\Entity\UserDepositCryptoaddress;
use App\Form\MultinodeRequestCreateType;
use App\Form\MultinodeRequestUpdateType;
use App\Integration\IntegrationException;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Rest\Route("/api/v1/multinode")
 */
class MultinodeController extends AbstractFOSRestController
{

    /**
     * Transaction to create
     *
     * @Rest\Route("/transactions/send/create", name="api_multinode_transactions_send_create", methods={"POST"})
     * @Rest\View()
     *
     * @SWG\Parameter(name="payload", description="Newly created transactions", required=true, in="body", required=true,
     *     @SWG\Schema(ref=@Model(type=App\Entity\MultinodeRequestCreate::class))
     * )
     *
     * @SWG\Response(response="200", description="Success",
     *     @SWG\Schema(type="object", example={"success": true})
     * )
     * @SWG\Response(response=400, description="Bad request")
     */
    public function transactionsSendCreate(Request $request, LoggerInterface $logger, EntityManagerInterface $em)
    {
        $logger->info("Incoming Multinode request /transactions/send/create: {$request->getContent()}");

        $multinodeRequestCreate = new MultinodeRequestCreate();

        $form = $this->createForm(MultinodeRequestCreateType::class, $multinodeRequestCreate);

        $form->submit($request->request->all(), false);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($multinodeRequestCreate);

            /** @var MultinodeRequestCreateItem $item */
            foreach ($multinodeRequestCreate->getReport() as $item) {
                $item->setMultinodeRequestCreate($multinodeRequestCreate);
                $em->persist($item);

                $withdrawal = $em->getRepository(CryptoWithdrawal::class)
                    ->findOneByTxDbId($item->getTxDbId());

                if (! $withdrawal instanceof CryptoWithdrawal) {
                    throw new IntegrationException("Unknown transaction with txDbId={$item->getTxDbId()} (create)");
                }

                if (is_null($withdrawal->getTxId())) {
                    $withdrawal->setTxId($item->getTxId());
                    $logger->info("Updated txId={$withdrawal->getTxId()} for CryptoWithdrawal id={$withdrawal->getId()}");
                }
            }

            $em->flush();

            return ['success' => true];
        }

        $logger->warning("Failed Multinode incoming request validation: {$form->getErrors()}; Request: {$request->getContent()}");

        return $form;
    }

    /**
     * Transaction to update
     *
     * @Rest\Route("/transactions/update", name="api_multinode_transactions_update", methods={"POST"})
     * @Rest\View()
     *
     * @SWG\Parameter(name="payload", description="Transactions updates", required=true, in="body", required=true,
     *     @SWG\Schema(ref=@Model(type=App\Entity\MultinodeRequestUpdate::class))
     * )
     *
     * @SWG\Response(response="200", description="Success",
     *     @SWG\Schema(type="object", example={"success": true})
     * )
     * @SWG\Response(response=400, description="Bad request")
     */
    public function transactionsUpdate(Request $request, LoggerInterface $logger, EntityManagerInterface $em, SerializerInterface $serializer)
    {
        $logger->info("Incoming Multinode request /transactions/update: {$request->getContent()}");

        $multinodeRequestUpdate = new MultinodeRequestUpdate();

        $form = $this->createForm(MultinodeRequestUpdateType::class, $multinodeRequestUpdate);

        $form->submit($request->request->all(), false);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($multinodeRequestUpdate);

            /** @var MultinodeRequestUpdateItem $item */
            foreach ($multinodeRequestUpdate->getReport() as $item) {
                $item->setMultinodeRequestUpdate($multinodeRequestUpdate);
                $em->persist($item);
                /** @var MultinodeRequestUpdateItemResults $results */
                foreach ($item->getTxUpdateResults() as $results) {
                    $results->setMultinodeRequestUpdateItem($item);
                    $em->persist($results);

                    // TODO: refactor to service

                    $user = $em->getRepository(User::class)->find($results->getUser());
                    if (! $user) {
                        throw new IntegrationException("Unknown user with id={$results->getUser()}");
                    }

                    /** @var Currency $currency */
                    $currency = $em->getRepository(Currency::class)->findOneByCode($results->getChain());
                    if (! $currency) {
                        throw new IntegrationException("Unknown currency with code={$results->getChain()}");
                    }

                    if ('send' == $results->getCategory()) {
                        $withdrawal = $em->getRepository(CryptoWithdrawal::class)
                            ->findOneByTxDbId([$results->getTxDbId()]);

                        if ('broker' == $results->getUser()) {
                            $logger->debug("Ignoring internal broker's transaction with txDbId={$results->getTxDbId()}");
                            continue;
                        }

                        if (! $withdrawal instanceof CryptoWithdrawal) {
                            throw new IntegrationException("Unknown transaction with txDbId={$results->getTxDbId()} (update)");
                        }

                        if (!is_null($results->getFee())) {
                            $withdrawal->setFee($results->getFee());
                            $logger->info("Updated fee={$withdrawal->getFee()} for CryptoWithdrawal id={$withdrawal->getId()}");
                        }

                        if ('confirmed' == $results->getUpdateState()) {
                            $withdrawal->setChainConfirmedAt(new \DateTime());
                            $logger->info("CryptoWithdrawal id={$withdrawal->getId()} is confirmed");

                            $account = $user->getPrimaryAccountByCurrency($currency);
                            if (!$account) {
                                throw new \LogicException("User {$user->getEmail()} has no primary account in {$currency->getCode()} for withdrawal");
                            }

                            // main transaction
                            {
                                $transaction = (new Transaction())
                                    ->setType(Transaction::TYPE_WITHDRAW)
                                    ->setAmount($withdrawal->getAmount())
                                    ->setUserAccount($account)
                                    ->setDescription("Crypto withdrawal to {$results->getAddressTo()} from {$results->getAddressFrom()}, txid={$withdrawal->getTxId()}");

                                $em->persist($transaction);
                            }

                            // fee transaction
                            {
                                if ($withdrawal->getFee() > 0) {
                                    $transaction = (new Transaction())
                                        ->setType(Transaction::TYPE_FEE)
                                        ->setAmount($withdrawal->getFee())
                                        ->setUserAccount($account)
                                        ->setDescription("Fee for crypto deposit transaction, txid={$withdrawal->getTxId()}");

                                    $em->persist($transaction);
                                }
                            }

                        }
                    } elseif ('receive' == $results->getCategory()) {
                        $depositCryptoaddress = $em->getRepository(UserDepositCryptoaddress::class)
                            ->findOneBy([
                                'user' => $user,
                                'address' => $results->getAddress()
                            ]);
                        if (! $depositCryptoaddress) {
                            throw new IntegrationException("Unknown deposit cryptoaddress with id={$results->getAddress()} for user id={$user->getId()}");
                        }

                        $deposit = $em->getRepository(CryptoDeposit::class)->findOneByTxDbId($results->getTxDbId());

                        if (! $deposit) {
                            $deposit = new CryptoDeposit();
                            $em->persist($deposit);
                        }

                        $deposit
                            ->setUser($user)
                            ->setCurrency($currency)
                            ->setCryptoaddress($depositCryptoaddress)
                            ->setAmount($results->getAmount())
                            ->setTxDbId($results->getTxDbId())
                            ->setTxId($results->getTxid())
                            ->setConfirmations($results->getConfirmations());

                        // get receiveFee or fee
                        if (!is_null($results->getReceiveFee())) {
                            $deposit->setFee($results->getReceiveFee());
                            if (!$results->getReceiveFeeCurrency()) {
                                throw new IntegrationException("Got receiveFee={$results->getReceiveFee()} but receiveFeeCurrency not set");
                            }
                            $feeCurrency = $em->getRepository(Currency::class)->findOneByCode($results->getReceiveFeeCurrency());
                        } elseif (!is_null($results->getFee())) {
                            $deposit->setFee($results->getFee());
                            $feeCurrency = $currency;
                        }

                        if ('confirmed' == $results->getUpdateState()) {
                            $deposit->setChainConfirmedAt(new \DateTime());
                            $logger->info("CryptoDeposit id={$deposit->getId()} is confirmed");

                            $account = $user->getPrimaryAccountByCurrency($currency);
                            if (!$account) {
                                throw new \LogicException("User {$user->getEmail()} has no primary account in {$currency->getCode()} for deposit");
                            }

                            // main transaction
                            {
                                $transaction = (new Transaction())
                                    ->setType(Transaction::TYPE_DEPOSIT)
                                    ->setAmount($deposit->getAmount())
                                    ->setUserAccount($account)
                                    ->setDescription("Crypto deposit to {$deposit->getCryptoaddress()->getAddress()} from {$results->getAddressFrom()}, txid={$deposit->getTxId()}");

                                $em->persist($transaction);
                            }

                            $feeAccount = $user->getPrimaryAccountByCurrency($feeCurrency);
                            if (!$account) {
                                throw new \LogicException("User {$user->getEmail()} has no primary account in {$feeCurrency->getCode()} for fee charging");
                            }

                            // fee transaction
                            {
                                if ($deposit->getFee() > 0) {
                                    $transaction = (new Transaction())
                                        ->setType(Transaction::TYPE_FEE)
                                        ->setAmount($deposit->getFee())
                                        ->setUserAccount($feeAccount)
                                        ->setDescription("Fee for crypto deposit transaction, txid={$deposit->getTxId()}");

                                    $em->persist($transaction);
                                }
                            }

                            /*if (in_array($currency->getCode(), ['BTC', 'BCH', 'LTC', 'DASH'])) {
                                // todo: create transactions
                            } elseif (in_array($currency->getCode(), ['ETH', 'USDT'])) {
                                // todo: wait for coinbase confirmed, DO NOT create transactions
                            } else {
                                $logger->warning("Chain confirmation for currency code={$currency->getCode()} not supported, ignored");
                            }*/
                        }
                    } else {
                        throw new IntegrationException("Unknown update category='{$results->getCategory()}': expected 'send' or 'receive'");
                    }
                }
            }

            $em->flush();

            return ['success' => true];
        }

        $logger->warning("Failed Multinode incoming request validation: {$form->getErrors()}; Request: {$request->getContent()}");

        return $form;
    }


}