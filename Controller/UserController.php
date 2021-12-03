<?php

namespace App\Controller;

use App\AML\AMLInspectionInterface;
use App\Entity\Currency;
use App\Entity\FiatWithdrawal;
use App\Entity\Model\CryptoAddress\Balance;
use App\Entity\Model\Info\WithdrawalInfo;
use App\Entity\Model\Pagination\PaginationResponse;
use App\Entity\Referal;
use App\Entity\ReferralEvent;
use App\Entity\SmsCode;
use App\Entity\Transfer;
use App\Entity\User;
use App\Entity\UserAccount;
use App\Entity\UserDepositCryptoaddress;
use App\Entity\UserPaymentMethod;
use App\Entity\UserSecretQuestionAnswer;
use App\Entity\UserWatchlist;
use App\Entity\UserWhiteIp;
use App\Entity\UserWithdrawAccount;
use App\Entity\UserWithdrawCryptoaddress;
use App\Entity\CryptoWithdrawal;
use App\Export\Account\ExportAccountService;
use App\Export\Transaction\ExportTransactionService;
use App\Finance\AccountService;
use App\Finance\TransferService;
use App\Form\FiatWithdrawalType;
use App\Form\PasswordChangeFormType;
use App\Form\PasswordRecoveryFormType;
use App\Form\RegistrationFormType;
use App\Form\SmsCodeCheckType;
use App\Form\TransferType;
use App\Form\UserPaymentMethodType;
use App\Form\UserSecretQuestionAnswerType;
use App\Form\SmsCodeSendType;
use App\Form\UserWatchlistType;
use App\Form\UserWhiteIpType;
use App\Form\UserWithdrawAccountType;
use App\Form\UserWithdrawCryptoaddressType;
use App\Form\CryptoWithdrawalType;
use App\Form\WithdrawalSmsCodeCheckType;
use App\Integration\BCCService;
use App\Integration\GetIDService;
use App\Integration\IntegrationException;
use App\Integration\MultinodeService;
use App\Repository\CurrencyRepository;
use App\Repository\TransactionRepository;
use App\Repository\UserChangePasswordRequestRepository;
use App\Repository\UserDepositCryptoaddressRepository;
use App\Repository\UserWithdrawAccountRepository;
use App\Repository\UserPaymentMethodRepository;
use App\Repository\UserRepository;
use App\Repository\UserSecretQuestionAnswerRepository;
use App\Repository\UserWatchlistRepository;
use App\Repository\UserWhiteIpRepository;
use App\Repository\UserWithdrawCryptoaddressRepository;
use App\Service\PasswordRecoveryService;
use App\Verification\Monolith\AMLService;
use App\Verification\Monolith\MonolithServiceInterface;
use App\Verification\ScanUploader;
use App\Verification\SmsServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\QrCode;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use FOS\RestBundle\Controller\Annotations\QueryParam;

/**
 * @Rest\Route("/api/v1/users")
 */
class UserController extends AbstractFOSRestController
{
    private $transactionSortFields = [
        'created_at' => 'createdAt',
        'type'       => 'type',
        'amount'     => 'amount',
    ];


    /**
     * @Rest\Route("", name="api_user_list", methods={"GET"})
     * @Rest\View(serializerGroups={"list"})
     *
     * @SWG\Response(response="200", description="List of users",
     *     @SWG\Schema(type="object", example={
     *     {
     *      "id": "08711617-ea9f-11e9-9632-00ff7ec4ff84",
     *      "email": "test@test.com",
     *      "first_name": "Test",
     *      "last_name": "Test",
     *      "middle_name": "Test",
     *      "timezone": 3
     *     }
     *    })
     * )
     *
     */
    public function index(UserRepository $repository)
    {
        // TODO: only for admin

        return $repository->findAll();
    }

    /**
     * @Rest\Route("/current", name="api_currentuser_show", methods={"GET"})
     * @Rest\View(serializerGroups={"profile"})
     *
     * @SWG\Response(response="200",  description="Current user profile",
     *     @SWG\Schema(ref=@Model(type=App\Entity\User::class))
     * )
     */
    public function showCurrent(Request $request)
    {
        return $this->getUser();
    }

    /**
     * @Rest\Route("/current/config", name="api_currentuser_show_config", methods={"GET"})
     *
     * @SWG\Response(response="200", description="Current user config")
     */
    public function showCurrentConfig(MonolithServiceInterface $monolithService)
    {
        return new Response($monolithService->getConfig($this->getUser()));
    }

    /**
     * @Rest\Route("/current/uastoken", name="api_currentuser_show_uastoken", methods={"GET"})
     * @Rest\View
     *
     * @SWG\Response(response="200", description="Current user UAS token")
     */
    public function showUasToken(MonolithServiceInterface $monolithService)
    {
        return ['token' => $this->getUser()->getUasToken()];
    }

    /**
     * @Rest\Route("/{id}", name="api_user_show", methods={"GET"})
     * @Rest\View(serializerGroups={"profile"})
     */
    public function show(Request $request, ?User $user)
    {
        if (is_null($user)) {
            throw $this->createNotFoundException("User not found");
        }

        if ($user != $this->getUser()) {
            throw $this->createAccessDeniedException("Permission denied to view user profile : {$request->get('id')}");
        }

        return $user;
    }

    /**
     * Get user's accounts list
     *
     * @Rest\Route("/{id}/accounts", name="api_user_accounts", methods={"GET"})
     * @Rest\View()
     *
     * @QueryParam(name="asset", description="The currency code to covert result in")
     * @QueryParam(name="type", description="Accounts type (1: Primary, 2: Hold)", nullable=true)
     * @QueryParam(name="export", description="Export type")
     *
     * @SWG\Response(response="200", description="List of user accounts",
     *     @SWG\Schema(type="array", @SWG\Items(ref=@Model(type=App\Entity\UserAccount::class)))
     * )
     */
    public function accounts(Request $request, ?User $user, ParamFetcher $paramFetcher,
                             AccountService $accountService, CurrencyRepository $currencyRepository, ExportAccountService $exportService)
    {
        if (is_null($user)) {
            throw $this->createNotFoundException("User not found");
        }

        if ($user != $this->getUser()) {
            throw $this->createAccessDeniedException("Permission denied to view user accounts: {$request->get('id')}");
        }

        $type = $paramFetcher->get('type');

        $asset = $paramFetcher->get('asset');

        if (!$asset) {
            $accounts = is_null($type)
                ? $accountService->getUserAvalable($user)
                : $user->getUserAccounts($type);
        } else {
            $currencyIn = $currencyRepository->findOneByCode($asset);
            if (!$currencyIn) {
                throw new BadRequestHttpException("Invalid currency: $asset");
            }

            $accounts = is_null($type)
                ? $accountService->getWithRates($accountService->getUserAvalable($user), $currencyIn)
                : $accountService->getWithRates($user->getUserAccounts($type), $currencyIn);
        }

        if ($exportType = $paramFetcher->get('export')) {
            return $exportService->getStreamedResponse($accounts, $exportType);
        }

        return $accounts;
    }

    /**
     * Get recipient's accounts list
     *
     * @Rest\Route("/{id}/transfer/{phone}/accounts", name="api_user_transfer_accounts", methods={"GET"})
     * @Rest\View(serializerGroups={"list"})
     *
     * @QueryParam(name="asset", description="The currency code to covert result in")
     * @QueryParam(name="type", description="Accounts type (1: Primary, 2: Hold)", nullable=true)
     * @QueryParam(name="export", description="Export type")
     *
     * @SWG\Response(response="200", description="List of user accounts",
     *     @SWG\Schema(type="array", @SWG\Items(ref=@Model(type=App\Entity\UserAccount::class)))
     * )
     */
    public function getTransferAccounts(Request $request, ?User $user, string $phone, UserRepository $userRepository,
                                     CurrencyRepository $currencyRepository, ParamFetcher $paramFetcher,
                                     AccountService $accountService)
    {
        if (is_null($user)) {
            throw $this->createNotFoundException("User not found");
        }

        if ($user != $this->getUser()) {
            throw $this->createAccessDeniedException("Permission denied to select transfer accounts for user {$request->get('email')}");
        }

        $phoneCanonical = preg_replace('/\D/', '', $phone);

        $recipient = $userRepository->findOneByPhone($phoneCanonical);

        if (! $recipient instanceof User) {
            throw $this->createNotFoundException("Recipient with phone {$phoneCanonical} not found");
        }

        return array_values(array_filter(
            $recipient->getUserAccounts(UserAccount::TYPE_PRIMARY),
                function (UserAccount $account) {
                    return $account->getCurrency()->getType() == Currency::TYPE_CRYPTO;
                }
            )
        );

    }

    /**
     * @Rest\Route("/{id}/accounts/total", name="api_user_accounts_total", methods={"GET"})
     * @Rest\View()
     *
     * @QueryParam(name="asset", description="The currency code to covert result in")
     *
     * @SWG\Response(response="200", description="Total amount",@SWG\Schema(type="string", example="0.01640581"))
     * @SWG\Response(response=400, description="Bad request", @SWG\Schema(type="object", example={"code": 400, "message": "Unknown currency USDT"}))
     * @SWG\Response(response=404, description="User not found")
     */
    public function accountsTotal(Request $request, ParamFetcher $paramFetcher,
                                  CurrencyRepository $currencyRepository, AccountService $accountService, ?User $user)
    {
        if (is_null($user)) {
            throw $this->createNotFoundException("User not found");
        }

        if ($user != $this->getUser()) {
            throw $this->createAccessDeniedException("Permission denied to view user accounts: {$request->get('id')}");
        }

        /** @var Currency $currency */
        $currency = $currencyRepository->findOneByCode($paramFetcher->get('asset'));

        if (is_null($currency)) {
            throw new BadRequestHttpException("Unknown currency {$paramFetcher->get('asset')}");
        }

        return $accountService->getUserTotalIn(
            $accountService->getUserAvalable($user),
            $currency,
            $currencyRepository->findBaseCurrency()
        );
    }

    /**
     * @Rest\Route("/{id}/accounts/profit", name="api_user_accounts_profit", methods={"GET"})
     * @Rest\View()
     *
     * @QueryParam(name="period", description="The period to show profit for: ['hour', 'today', 'week']")
     *
     * @SWG\Response(response="200", description="Profit percent",@SWG\Schema(type="string", example="2.15318627"))
     * @SWG\Response(response=400, description="Bad request", @SWG\Schema(type="object", example={"code": 400, "message": "Unknown currency USDT"}))
     * @SWG\Response(response=404, description="User not found", @SWG\Schema(type="object", example={"code": 404, "message": "User not found"}))
     */
    public function accountsProfit(Request $request, ParamFetcher $paramFetcher, ?User $user)
    {
        if (is_null($user)) {
            throw $this->createNotFoundException("User not found");
        }

        if ($user != $this->getUser()) {
            throw $this->createAccessDeniedException("Permission denied to view user accounts: {$request->get('id')}");
        }

        return $user->getAccountsProfit($paramFetcher->get('period'));
    }



    /**
     * @Rest\Route("/{id}/transactions", name="api_user_transactions", methods={"GET"})
     * @Rest\View()
     *
     * @QueryParam(name="sort_by", description="Field to sort by", default="created_at")
     * @QueryParam(name="order", description="Sort order: ASC, DESC", default="DESC")
     * @QueryParam(name="page", description="Page number", default=1)
     * @QueryParam(name="size", description="Page size", default=10)
     * @QueryParam(name="search", description="Search string", nullable=true)
     * @QueryParam(name="export", description="Export type")
     *
     * @SWG\Response(response="200", description="List of user transactions",
     *     @SWG\Schema(type="array", @SWG\Items(ref=@Model(type=App\Entity\Transaction::class)))
     * )
     */
    public function transactions(Request $request, ?User $user, ParamFetcher $paramFetcher,
                                 TransactionRepository $repository, PaginatorInterface $paginator, ExportTransactionService $exportService)
    {
        if (is_null($user)) {
            throw $this->createNotFoundException("User not found");
        }

        if ($user != $this->getUser()) {
            throw $this->createAccessDeniedException("Permission denied to view user transactions: {$request->get('id')}");
        }

        $pagination = $paginator->paginate(
            $repository->getByUserQb(
                $this->getUser(),
                $this->transactionSortFields[$paramFetcher->get('sort_by')],
                $paramFetcher->get('order'),
                $paramFetcher->get('search')
            ),
            $paramFetcher->get('page'),
            $paramFetcher->get('size')
        );

        if ($exportType = $paramFetcher->get('export')) {
            return $exportService->getStreamedResponse($pagination, $exportType);
        }

        return new PaginationResponse($pagination);
    }

    /**
     * Create / update user
     *
     * @Rest\Route("/register", name="api_user_create", methods={"POST"})
     * @Rest\Route("/{id}", name="api_user_update", methods={"PUT"})
     * @Rest\View(serializerGroups={"profile"})
     *
     * @SWG\Parameter(name="payload", description="User data", required=true, in="body", required=true, @SWG\Schema(ref=@Model(type=App\Entity\User::class)))
     * @SWG\Response(response=200, description="Success registration",
     *     @SWG\Schema(
     *         type="object",
     *         example={"success": true}
     *     )
     * )
     * @SWG\Response(response=400, description="Validation failed",
     *     @SWG\Schema(
     *         type="object",
     *         example={
     *          "code": 400,
     *          "message": "Validation Failed",
     *          "errors": {}
     *        }
     *     )
     * )
     *
     */
    public function update(Request $request, ?User $user, UserPasswordEncoderInterface $passwordEncoder,
                           MonolithServiceInterface $monolithService, LoggerInterface $logger, GetIDService $getIDService, EntityManagerInterface $em)
    {
        if (is_null($request->get('id'))) {
            // create user
            $user = new User();
        } else {
            // check if user exists
            if (! $user instanceof User) {
                throw $this->createNotFoundException("Not found user with id: {$request->get('id')}");
            }

            // check if user updates his own profile
            if ($user != $this->getUser()) {
                throw $this->createAccessDeniedException("Permission denied to update user: {$request->get('id')}");
            }
        }

        $form = $this->createForm(RegistrationFormType::class, $user);

        $form->submit($request->request->all(), false);

        if ($form->isSubmitted() && $form->isValid()) {
            // referral hash
            $hash = $form->get('hash')->getData();
            if (!is_null($hash)) {
                $referral = $em->getRepository(Referal::class)->findOneBy([
                    'hash' => $hash,
                    'email' => $form->get('email')->getData(),
                    'registeredAt' => null
                ]);
                if (!$referral instanceof Referal) {
                    throw $this->createNotFoundException("Not found referral with hash={$hash} or it has been used");
                }
                $referral->setRegisteredAt(new \DateTimeImmutable());
                $event = (new ReferralEvent())
                    ->setEvent(ReferralEvent::TYPE_REGISTRATION)
                    ->setEmail($form->get('email')->getData())
                    ->setEventDate(new \DateTimeImmutable())
                    ->setEventData('{"data":{}}')
                    ->setAccepted(false);
                $em->persist($event);

                $referralHashedPassword = password_hash(
                    $form->get('plain_password')->getData(),
                    PASSWORD_BCRYPT)
                ;

               $user->setReferralPasswordHash($referralHashedPassword);
            }

            if ($plainPassword = $form->get('plain_password')->getData()) {
                $user->setPassword(
                    $passwordEncoder->encodePassword($user, $plainPassword)
                );
            }

            $em->persist($user);

            // Если профиль заполнен полностью - отсылать в монолит
            if (!empty($form->get('identity_type')->getData())) {
                if (empty($user->getPhone())) {
                    throw new BadRequestHttpException("Missing user phone number");
                }
                //$monolithService->createUser($user);
            }

            if ($form->get('sent_to_getid')->getData()) {
                // очищаем getid данные
                $user->setStatus(User::STATUS_SENT_TO_GETID);
                $user->setNotMatchedGetidData(null);
                $user->setGetidData(null);
                $logger->info('Cleared GetID result for user ' . $user->getEmail() . ' on updating user form');
            }

            if ($form->get('completed')->getData()) {
                // форма заполнена
                $user->setStatus(User::STATUS_FORM_COMPLETED);
                $logger->info('User ' . $user->getEmail() . ' completed the form');

                if (empty($user->getGetidData())) {
                    $logger->info("Comparing ignored since user {$user->getEmail()} has no GetID data");
                } else {
                    // делаем сверку
                    // todo: refactor to UserService
                    $notMatchedFields = $getIDService->compare($user);
                    $user->setNotMatchedGetidData($notMatchedFields);
                    if (empty($notMatchedFields)) {
                        $user->setIsVerificationSent(true);
                        $user->setStatus(User::STATUS_SENT_TO_MONOLITH);
                        $monolithService->createUser($user);
                        $logger->info('User data compared, sending to Monolith user ' . $user->getEmail());
                    } else {
                        $user->setStatus(User::STATUS_GETID_NOT_MATCHED_WITH_FORM);
                        $logger->info("Not matching user {$user->getEmail()} GetID data: " . \json_encode($notMatchedFields));
                    }

                }
            }

            $em->flush();


            // todo: make finalization request?
            /*if (!empty($form->get('additional_politic_person')->getData())) {
                if (empty($user->getImageSelfie())) {
                    throw new BadRequestHttpException("Missing selfie image");
                }
                if (empty($user->getImageDocument())) {
                    throw new BadRequestHttpException("Missing document image");
                }
            }*/

            return ['success' => true];
        }

        return $form;
    }

    /**
     * Updates user data from GetID
     *
     * @Rest\Route("/getid", name="api_user_update_getid_data", methods={"POST"})
     * @Rest\View()
     *
     */
    public function updateGetidData(Request $request, EntityManagerInterface $em, LoggerInterface $logger,
                                    GetIDService $getIDService, BCCService $bccService, MonolithServiceInterface $monolithService)
    {
        $data = \json_decode($request->getContent());

        foreach ($data->application->fields as $field) {
            if ($field->category == 'user_id') {
                $userId = $field->content;
            }
        }

        if (empty($userId)) {
            throw new BadRequestHttpException("Missing user_id");
        }

        /** @var User $user */
        $user = $em->getRepository(User::class)->find($userId);

        if (!$user) {
            throw $this->createNotFoundException("User with id $userId not found");
        }

        $logger->info("Incoming GetID data for user {$user->getEmail()}: " . $request->getContent());

        $data = \json_decode($request->getContent());

        $user->setGetidData($data);

        $notMatchedFields = $getIDService->compare($user);

        $user->setNotMatchedGetidData($notMatchedFields);

        if (empty($notMatchedFields)) {
            $logger->info('User data compared, sending to Monolith user ' . $user->getEmail());
            $user->setIsVerificationSent(true);
            $user->setStatus(User::STATUS_SENT_TO_MONOLITH);
            //$bccService->createUser($user);
            $monolithService->createUser($user);
        } else {
            $logger->info("Not matching user {$user->getEmail()} GetID data: " . \json_encode($notMatchedFields));
            $user->setStatus(User::STATUS_GETID_NOT_MATCHED_WITH_FORM);
        }

        $em->flush();

        return ['success' => true];
    }

    /**
     * Clears the result of comparing with GetId
     *
     * @Rest\Route("/current/getid/clear", name="api_user_clear_getid_data", methods={"POST"})
     * @Rest\View()
     *
     */
    public function clearGetidData(EntityManagerInterface $em, LoggerInterface $logger)
    {
        /** @var User $user */
        $user = $this->getUser();

        $user->setNotMatchedGetidData(null);
        $user->setGetidData(null);
        $user->setStatus(User::STATUS_SENT_TO_GETID);

        $logger->info('Cleared GetID result for user ' . $user->getEmail());

        $em->flush();

        return ['success' => true];
    }

    /**
     * Gets stored user GetID data
     *
     * @Rest\Route("/current/getid", name="api_user_get_getid_data", methods={"GET"})
     * @Rest\View()
     *
     */
    public function getGetidData()
    {
        /** @var User $user */
        $user = $this->getUser();

        return $user->getGetidData();
    }

    /**
     * Gets user Monolith status
     *
     * @Rest\Route("/current/monolith", name="api_user_get_monolith_status", methods={"GET"})
     * @Rest\View()
     *
     */
    public function getMonolithStatus(MonolithServiceInterface $monolithService)
    {
        /** @var User $user */
        $user = $this->getUser();

        $result = $monolithService->getUserStatus($user);

        return $result;
    }

    /**
     * Gets whether GetID data is compared (identical to user's stored data)
     *
     * @Rest\Route("/current/getid/is_compared", name="api_user_get_getid_iscompared", methods={"GET"})
     * @Rest\View()
     *
     */
    public function getIsGetidCompared()
    {
        /** @var User $user */
        $user = $this->getUser();

        $notMatchedFields = $user->getNotMatchedGetidData();

        return $user->isGetidMatched()
            ? ['matched' => true]
            : ['matched' => false, 'fields' => $notMatchedFields];
    }

    /**
     * Recovers user password (send link to email)
     *
     * @Rest\Route("/password-recovery", name="api_user_password_recovery", methods={"POST"})
     * @Rest\View()
     *
     */
    public function recoveryPassword(Request $request, UserRepository $repository, PasswordRecoveryService $service)
    {
        $form = $this->createForm(PasswordRecoveryFormType::class, null);

        $form->submit($request->request->all(), false);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->getData()['email'];

            /** @var User|null $existed */
            $existed = $repository->findOneByEmail($email);

            if (!$existed) {
                throw new NotFoundHttpException("User with email {$email} not found");
            }

            $service->recoverPassword($existed);

            return ['success' => true];
        }

        return $form;
    }

    /**
     * Changes the password
     *
     * @Rest\Route("/password-change", name="api_user_password_change", methods={"POST"})
     * @Rest\View()
     *
     */
    public function changePassword(Request $request, UserChangePasswordRequestRepository $repository,
                                   EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder)
    {
        $form = $this->createForm(PasswordChangeFormType::class, null);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $changePasswordRequest = $repository->findOneBy([
                'email'  => $form->getData()['email'],
                'token'  => $form->getData()['token'],
                'usedAt' => null
            ]);

            if (!$changePasswordRequest) {
                throw new NotFoundHttpException("Email and token don't match or have been used");
            }

            $user = $changePasswordRequest->getUser();

            $plainPassword = $form->get('plain_password')->getData();

            $user->setPassword($passwordEncoder->encodePassword($user, $plainPassword));

            $changePasswordRequest->setUsedAt(new \DateTime());

            $em->flush();

            return ['success' => true];
        }

        return $form;
    }

    /**
     * @Rest\Route("/{id}/scans", name="api_user_scans", methods={"POST"})
     * @Rest\FileParam(name="document", description="Scan of user's document", nullable=true, image=true, requirements={"maxSize"="10M", "minWidth"="500", "minHeight"="500"})
     * @Rest\FileParam(name="selfie", description="User's selfie", nullable=true, image=true, requirements={"maxSize"="10M", "minWidth"="500", "minHeight"="500"})
     * @Rest\View()
     *
     * @SWG\Parameter(name="document", description="Scan of user's document", in="formData", type="file")
     * @SWG\Parameter(name="selfie", description="User's selfie", in="formData", type="file")
     * @SWG\Response(response=200, description="Success upload", @SWG\Schema(type="object",example={"success": true}))
     *
     */
    public function uploadImages(Request $request, ParamFetcher $paramFetcher, ?User $user, ScanUploader $uploader)
    {
        if (is_null($user)) {
            throw $this->createNotFoundException("User not found");
        }

        if ($user != $this->getUser()) {
            throw $this->createAccessDeniedException("Permission denied to load scans for user {$request->get('id')}");
        }

        if (0 === $request->files->count()) {
            throw new BadRequestHttpException("No files attached");
        }

        /** @var UploadedFile $scan */
        foreach ($request->files->all() as $type => $file) {
            $scan = $paramFetcher->get($type);
            if ($scan) {
                $uploader->upload($type, $scan, $user);
            }
        }

        return ['success' => true];
    }

    /**
     * Create / update user payment method
     *
     * @Rest\Route("/{id}/payment_method", name="api_user_paymentmethod_create", methods={"POST"})
     * @Rest\Route("/{id}/payment_method/{pmId}", name="api_user_paymentmethod_update", methods={"PUT"})
     * @Rest\View()
     *
     * @SWG\Parameter(name="payload", description="User payment method", required=true, in="body", required=true, @SWG\Schema(
     *     type="object",
     *     example= {
     *       "asset": 4,
     *       "title": "Payment Method title",
     *       "address": "Some address"
     *     }
     * ))
     * @SWG\Response(response=200, description="Success",
     *     @SWG\Schema(
     *         type="object",
     *         example={"success": true}
     *     )
     * )
     * @SWG\Response(response=400, description="Validation failed",
     *     @SWG\Schema(
     *         type="object",
     *         example={
     *          "code": 400,
     *          "message": "Validation Failed",
     *          "errors": {}
     *        }
     *     )
     * )
     *
     */
    public function paymentMethod(Request $request, ?User $user, UserPaymentMethodRepository $pmRepository)
    {
        // check if user exists
        if (! $user instanceof User) {
            throw $this->createNotFoundException("Not found user with id: {$request->get('id')}");
        }

        // check if user updates his own profile
        if ($user != $this->getUser()) {
            throw $this->createAccessDeniedException("Permission denied to update user: {$request->get('id')}");
        }

        if ($pmId = $request->get('pmId')) {
            $paymentMethod = $pmRepository->findOneBy([
                'user' => $user,
                'id' => $pmId
            ]);

            if (! $paymentMethod instanceof UserPaymentMethod) {
                throw $this->createNotFoundException("Payment method not found");
            }
        } else {
            $paymentMethod = new UserPaymentMethod();
        }

        $form = $this->createForm(UserPaymentMethodType::class, $paymentMethod);

        $form->submit($request->request->all(), false);

        if ($form->isSubmitted() && $form->isValid()) {
            $paymentMethod->setUser($user);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($paymentMethod);
            $entityManager->flush();

            return ['success' => true];
        }

        return $form;
    }

    /**
     * Create / update user white IP
     *
     * @Rest\Route("/{id}/white_ip", name="api_user_whiteip_create", methods={"POST"})
     * @Rest\Route("/{id}/white_ip/{ipId}", name="api_user_whiteip_update", methods={"PUT"})
     * @Rest\View()
     *
     * @SWG\Parameter(name="payload", description="User white IP", required=true, in="body", required=true, @SWG\Schema(
     *     type="object",
     *     example= {
     *       "ip": "10.195.1.20"
     *     }
     * ))
     * @SWG\Response(response=200, description="Success",
     *     @SWG\Schema(
     *         type="object",
     *         example={"success": true}
     *     )
     * )
     * @SWG\Response(response=400, description="Validation failed",
     *     @SWG\Schema(
     *         type="object",
     *         example={
     *          "code": 400,
     *          "message": "Validation Failed",
     *          "errors": {}
     *        }
     *     )
     * )
     *
     */
    public function whiteIp(Request $request, ?User $user, UserWhiteIpRepository $pmRepository)
    {
        // check if user exists
        if (! $user instanceof User) {
            throw $this->createNotFoundException("Not found user with id: {$request->get('id')}");
        }

        // check if user updates his own profile
        if ($user != $this->getUser()) {
            throw $this->createAccessDeniedException("Permission denied to update user: {$request->get('id')}");
        }

        if ($ipId = $request->get('ipId')) {
            $whiteIp = $pmRepository->findOneBy([
                'user' => $user,
                'id' => $ipId
            ]);

            if (! $whiteIp instanceof UserWhiteIp) {
                throw $this->createNotFoundException("White IP not found");
            }
        } else {
            $whiteIp = new UserWhiteIp();
        }

        $form = $this->createForm(UserWhiteIpType::class, $whiteIp);

        $form->submit($request->request->all(), false);

        if ($form->isSubmitted() && $form->isValid()) {
            $whiteIp->setUser($user);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($whiteIp);
            $entityManager->flush();

            return ['success' => true];
        }

        return $form;
    }

    /**
     * Create / update user secret question answer
     *
     * @Rest\Route("/{id}/secret_question_answer", name="api_user_secretquestionanswer_create", methods={"POST"})
     * @Rest\Route("/{id}/secret_question_answer/{sqaId}", name="api_user_secretquestionanswer_update", methods={"PUT"})
     * @Rest\View()
     *
     * @SWG\Parameter(name="payload", description="User secret question answer", required=true, in="body", required=true, @SWG\Schema(
     *     type="object",
     *     example= {
     *       "question": 3,
     *       "answer": "This is my answer"
     *     }
     * ))
     * @SWG\Response(response=200, description="Success",
     *     @SWG\Schema(
     *         type="object",
     *         example={"success": true}
     *     )
     * )
     * @SWG\Response(response=400, description="Validation failed",
     *     @SWG\Schema(
     *         type="object",
     *         example={
     *          "code": 400,
     *          "message": "Validation Failed",
     *          "errors": {}
     *        }
     *     )
     * )
     *
     */
    public function secretQuestionAnswer(Request $request, ?User $user, UserSecretQuestionAnswerRepository $answerRepository)
    {
        // check if user exists
        if (! $user instanceof User) {
            throw $this->createNotFoundException("Not found user with id: {$request->get('id')}");
        }

        // check if user updates his own profile
        if ($user != $this->getUser()) {
            throw $this->createAccessDeniedException("Permission denied to update user: {$request->get('id')}");
        }

        if ($sqaId = $request->get('sqaId')) {
            $answer = $answerRepository->findOneBy([
                'user' => $user,
                'id' => $sqaId
            ]);

            if (! $answer instanceof UserSecretQuestionAnswer) {
                throw $this->createNotFoundException("Secret question answer not found");
            }
        } else {
            $answer = new UserSecretQuestionAnswer();
        }

        $form = $this->createForm(UserSecretQuestionAnswerType::class, $answer);

        $form->submit($request->request->all(), false);

        if ($form->isSubmitted() && $form->isValid()) {
            $answer->setUser($user);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($answer);
            $entityManager->flush();

            return ['success' => true];
        }

        return $form;
    }

    /**
     * Create watchlist
     *
     * @Rest\Route("/{id}/watchlist", name="api_user_watchlist_create", methods={"POST"})
     * @Rest\View()
     *
     * @SWG\Parameter(name="payload", description="User watchlist", required=true, in="body", required=true, @SWG\Schema(
     *     type="object",
     *     example= {
     *       "direcition": 2
     *     }
     * ))
     * @SWG\Response(response=200, description="Success",
     *     @SWG\Schema(
     *         type="object",
     *         example={"success": true}
     *     )
     * )
     * @SWG\Response(response=400, description="Validation failed",
     *     @SWG\Schema(
     *         type="object",
     *         example={
     *          "code": 400,
     *          "message": "Validation Failed",
     *          "errors": {}
     *        }
     *     )
     * )
     *
     */
    public function addWatchlist(Request $request, ?User $user)
    {
        // check if user exists
        if (! $user instanceof User) {
            throw $this->createNotFoundException("Not found user with id: {$request->get('id')}");
        }

        // check if user updates his own profile
        if ($user != $this->getUser()) {
            throw $this->createAccessDeniedException("Permission denied to add watchlist for user: {$request->get('id')}");
        }

        $watchlist = new UserWatchlist();

        $form = $this->createForm(UserWatchlistType::class, $watchlist);

        $form->submit($request->request->all(), false);

        if ($form->isSubmitted() && $form->isValid()) {
            $watchlist->setUser($user);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($watchlist);
            $entityManager->flush();

            return ['success' => true];
        }

        return $form;
    }

    /**
     * Delete watchlist
     *
     * @Rest\Route("/{id}/watchlist/{watchlistId}", name="api_users_watchlist_delete", methods={"DELETE"})
     * @Rest\View()
     *
     * @SWG\Parameter(name="id", description="User ID", required=true, in="path", type="string")
     * @SWG\Parameter(name="watchlistId", description="Watchlist ID", required=true, in="path", type="integer")
     * @SWG\Response(response="200", description="Success", @SWG\Schema(type="object", example={"success": true}))
     * @SWG\Response(response="409", description="Can not delete", @SWG\Schema(type="object", example={"code": 409, "message": "Cannot delete"}))
     * @SWG\Response(response="404", description="Not found", @SWG\Schema(type="object", example={"code": 404, "message": "Not found"}))
     */
    public function deleteWatchlist(Request $request, ?User $user, UserWatchlistRepository $wlRepository, EntityManagerInterface $em)
    {
        // check if user exists
        if (! $user instanceof User) {
            throw $this->createNotFoundException("Not found user with id: {$request->get('id')}");
        }

        // check if user updates his own profile
        if ($user != $this->getUser()) {
            throw $this->createAccessDeniedException("Permission denied to delete watchlist for user: {$request->get('id')}");
        }

        $watchlist = $wlRepository->findOneBy([
            'user' => $user,
            'id' => $request->get('watchlistId')
        ]);

        if (! $watchlist instanceof UserWatchlist) {
            throw $this->createNotFoundException("Watchlist not found");
        }

        try {
            $em->remove($watchlist);
            $em->flush();

            return ['success' => true];
        } catch (\Exception $e) {
            throw new ConflictHttpException("Can not delete watchlist {$request->get('watchlistId')}: " . $e->getMessage());
        }
    }

    /**
     * Send SMS-code to the user
     *
     * @Rest\Route("/{id}/send_sms_code", name="api_user_sendsmscode", methods={"POST"})
     * @Rest\View()
     *
     * @SWG\Parameter(name="payload", description="User's phone", required=true, in="body", required=true,
     *     @SWG\Schema(
     *          type="object",
     *          example={"phone": "+71234567890"}
     *     )
     * )
     * @SWG\Response(response=200, description="Success registration",
     *     @SWG\Schema(
     *         type="object",
     *         example={"success": true}
     *     )
     * )
     * @SWG\Response(response=400, description="Error",
     *     @SWG\Schema(
     *         type="object",
     *         example={"code": 400,"message": "Error sending SMS-code"}
     *     )
     * )
     *
     */
    public function sendSmsCode(Request $request, ?User $user, SmsServiceInterface $service,
                                EntityManagerInterface $em)
    {
        // check if user exists
        if (! $user instanceof User) {
            throw $this->createNotFoundException("Not found user with id: {$request->get('id')}");
        }

        // check if user sends sms himself
        if ($user != $this->getUser()) {
            throw $this->createAccessDeniedException("Permission denied to request code for user: {$request->get('id')}");
        }

        /*if (is_null($user->getPhone())) {
            throw new BadRequestHttpException("Missing phone number");
        }*/

        $smsCode = new SmsCode();

        $form = $this->createForm(SmsCodeSendType::class, $smsCode);

        $form->submit($request->request->all(), false);

        if ($form->isSubmitted() && $form->isValid()) {
            $smsCode->setUser($user);
            $service->sendCode($smsCode);

            $em->persist($smsCode);

            $user->setStatus(User::STATUS_PHONE_SENT);

            $em->flush();

            return ['success' => true];
        }

        return $form;
    }

    /**
     * Check sent SMS-code
     *
     * @Rest\Route("/{id}/check_sms_code", name="api_user_checksmscode", methods={"POST"})
     * @Rest\View()
     *
     * @SWG\Parameter(name="payload", description="User's phone", required=true, in="body", required=true,
     *     @SWG\Schema(
     *          type="object",
     *          example={"phone": "+71234567890", "code": "1010"}
     *     )
     * )
     * @SWG\Response(response=200, description="Success registration",
     *     @SWG\Schema(
     *         type="object",
     *         example={"success": true}
     *     )
     * )
     * @SWG\Response(response=400, description="Validation failed",
     *     @SWG\Schema(
     *         type="object",
     *         example={
     *          "code": 400,
     *          "message": "Invalid SMS code"
     *        }
     *     )
     * )
     *
     */
    public function checkSmsCode(Request $request, ?User $user, SmsServiceInterface $smsService, EntityManagerInterface $em)
    {
        // check if user exists
        if (! $user instanceof User) {
            throw $this->createNotFoundException("Not found user with id: {$request->get('id')}");
        }

        // check if user updates his own profile
        if ($user != $this->getUser()) {
            throw $this->createAccessDeniedException("Permission denied to update user: {$request->get('id')}");
        }

        $form = $this->createForm(SmsCodeCheckType::class, new SmsCode());

        $form->submit($request->request->all(), false);

        if ($form->isSubmitted() && $form->isValid()) {

            $smsCode = (new SmsCode())
                ->setCode($form->getData()->getCode())
                ->setPhone($form->getData()->getPhone())
                ->setUser($user);

            if (!$smsService->checkCode($smsCode)) {
                throw new BadRequestHttpException("Invalid sms code");
            }

            $user->setPhone($smsCode->getPhone());
            $user->setStatus(User::STATUS_PHONE_VERIFIED);

            $em->flush();

            return ['success' => true];
        }

        return $form;
    }

    /**
     * @Rest\Route("/{id}/deposit/cryptoaddresses", name="api_user_deposit_cryptoaddresses", methods={"GET", "POST"})
     * @Rest\View()
     *
     * @QueryParam(name="currency", description="The cryptocurrency code to generate new address for", nullable=false)
     *
     * @SWG\Response(response="200", description="List of user deposit cryptoaddresses",
     *     @SWG\Schema(type="array", @SWG\Items(ref=@Model(type=App\Entity\UserDepositCryptoaddress::class)))
     * )
     */
    public function depositCryptoAddresses(Request $request, ?User $user, ParamFetcher $paramFetcher, MultinodeService $multinodeService, CurrencyRepository $currencyRepository)
    {
        if (is_null($user)) {
            throw $this->createNotFoundException("User not found");
        }

        if ($user != $this->getUser()) {
            throw $this->createAccessDeniedException("Permission denied to view user deposit cryptoaddresses: {$request->get('id')}");
        }

        if (! $currencyCode = $paramFetcher->get('currency')) {
            throw new BadRequestHttpException("Currency not set");
        }

        // POST (Generate new address)
        if (Request::METHOD_POST === $request->getMethod()) {
            $currency = $currencyRepository->findOneByCode($currencyCode);

            if (! $currency instanceof Currency) {
                throw new BadRequestHttpException("Unknown currency code {$currencyCode}");
            }

            $multinodeService->generateCryptoAddress($currency, $user);

            return ['success' => true];
        }

        return array_values($user->getUserDepositCryptoaddresses()->filter(
            function (UserDepositCryptoaddress $cryptoaddress) use ($currencyCode) {
                return strtolower($currencyCode) == strtolower($cryptoaddress->getCurrency()->getCode());
            }
        )->toArray());
    }

    /**
     * @Rest\Route("/{id}/deposit/cryptoaddresses/{caId}/qr", name="api_user_deposit_cryptoaddress_qr", methods={"GET"})
     * @Rest\View()
     *
     * @SWG\Response(response="200", description="Deposit cryptoaddress QR code")
     * )
     */
    public function depositCryptoAddressQr(Request $request, ?User $user, UserDepositCryptoaddressRepository $repository)
    {
        if (is_null($user)) {
            throw $this->createNotFoundException("User not found");
        }

        if ($user != $this->getUser()) {
            throw $this->createAccessDeniedException("Permission denied to another user's {$request->get('id')} deposit cryptoaddresses");
        }

        $depositCryptoaddress = $repository->findOneBy([
            'user' => $user,
            'id' => $request->get('caId'),
        ]);

        if (!$depositCryptoaddress) {
            throw $this->createNotFoundException("Invalid cryptoaddress {$request->get('caId')} for user {$user->getEmail()}");
        }

        $qrCode = new QrCode($depositCryptoaddress->getAddress());

        return new Response($qrCode->writeString(), 200, [
            'Content-Type' => $qrCode->getContentType(),
        ]);
    }

    /**
     * @Rest\Route("/{id}/deposit/balance", name="api_user_deposit_balance", methods={"GET"})
     * @Rest\View()
     *
     * @QueryParam(name="currency", description="The currency code")
     *
     * @SWG\Response(response="200", description="User deposit balance",
     *     @SWG\Schema(
     *         type="object",
     *         example={"balance": "103056.00000000","withheld": "-195.00000000","withheld_converted": "-195.00000000","maximum_deposit": 10000}
     *     )
     * )
     */
    public function depositBalance(Request $request, ?User $user, ParamFetcher $paramFetcher, CurrencyRepository $currencyRepository)
    {
        if (is_null($user)) {
            throw $this->createNotFoundException("User not found");
        }

        if ($user != $this->getUser()) {
            throw $this->createAccessDeniedException("Permission denied to view user deposit cryptoaddress balance: {$request->get('id')}");
        }

        if (! $currencyCode = $paramFetcher->get('currency')) {
            throw new BadRequestHttpException("Currency parameter missing");
        }

        $currency = $currencyRepository->findOneByCode($currencyCode);

        if (!$currency) {
            throw new BadRequestHttpException("Unknown currency code '$currencyCode'");
        }

        $primaryAccount = $user->getPrimaryAccountByCurrency($currency);
        $holdAccount = $user->getHoldAccountByCurrency($currency);

        $balance = new Balance();
        $balance->balance = $primaryAccount->getBalance();
        $balance->withheld = $holdAccount->getBalance();
        $balance->withheldConverted = $holdAccount->getBalance();
        $balance->maximumDeposit = "10000.00000000";

        return $balance;
    }

    /**
     * @Rest\Route("/{id}/withdraw/accounts", name="api_user_withdraw_accounts", methods={"GET"})
     * @Rest\View()
     *
     * @QueryParam(name="currency", description="The currency code", nullable=false, strict=true)
     *
     * @SWG\Response(response="200", description="List of user withdraw accounts",
     *     @SWG\Schema(type="array", @SWG\Items(ref=@Model(type=App\Entity\UserWithdrawAccount::class)))
     * )
     */
    public function withdrawAccountsList(Request $request, ?User $user, ParamFetcher $paramFetcher,
                                         UserWithdrawAccountRepository $repository, CurrencyRepository $currencyRepository)
    {
        if (is_null($user)) {
            throw $this->createNotFoundException("User not found");
        }

        if ($user != $this->getUser()) {
            throw $this->createAccessDeniedException("Permission denied to view user transactions: {$request->get('id')}");
        }

        $currencyCode = $paramFetcher->get('currency');

        $currency = $currencyRepository->findOneByCode($currencyCode);

        if (! $currency instanceof Currency) {
            throw new BadRequestHttpException("Unknown currency code {$currencyCode}");
        }

        return $repository->findBy([
            'user' => $user,
            'currency' => $currency,
        ]);
    }

    /**
     * Create withdraw account
     *
     * @Rest\Route("/{id}/withdraw/accounts", name="api_user_withdraw_account_create", methods={"POST"})
     * @Rest\View()
     *
     * @QueryParam(name="currency", description="The currency code", nullable=false, strict=true)
     *
     * @SWG\Parameter(name="payload", description="Withdraw account", required=true, in="body", required=true, @SWG\Schema(ref=@Model(type=App\Entity\UserWithdrawAccount::class)))
     * @SWG\Response(response=200, description="Success",
     *     @SWG\Schema(
     *         type="object",
     *         example={"success": true}
     *     )
     * )
     * @SWG\Response(response=400, description="Validation failed",
     *     @SWG\Schema(
     *         type="object",
     *         example={
     *          "code": 400,
     *          "message": "Validation Failed",
     *          "errors": {}
     *        }
     *     )
     * )
     *
     */
    public function withdrawAccountsCreate(Request $request, ?User $user, EntityManagerInterface $em,
                                           ParamFetcher $paramFetcher, CurrencyRepository $currencyRepository)
    {
        // check if user exists
        if (! $user instanceof User) {
            throw $this->createNotFoundException("Not found user with id: {$request->get('id')}");
        }

        // check if user updates his own profile
        if ($user != $this->getUser()) {
            throw $this->createAccessDeniedException("Permission denied to update user: {$request->get('id')}");
        }

        $currencyCode = $paramFetcher->get('currency');

        $currency = $currencyRepository->findOneByCode($currencyCode);

        if (! $currency instanceof Currency) {
            throw new BadRequestHttpException("Unknown currency code {$currencyCode}");
        }

        $withdrawAccount = new UserWithdrawAccount();

        $form = $this->createForm(UserWithdrawAccountType::class, $withdrawAccount);

        $form->submit($request->request->all(), false);

        if ($form->isSubmitted() && $form->isValid()) {
            $withdrawAccount->setUser($user);
            $withdrawAccount->setCurrency($currency);

            $em->persist($withdrawAccount);
            $em->flush();

            return ['success' => true];
        }

        return $form;
    }

    /**
     * Delete user's withdraw fiat account
     *
     * @Rest\Route("/{id}/withdraw/accounts/{waId}", name="api_user_withdraw_account_delete", methods={"DELETE"})
     * @Rest\View()
     *
     * @SWG\Response(response=200, description="Success",
     *     @SWG\Schema(
     *         type="object",
     *         example={"success": true}
     *     )
     * )
     */
    public function withdrawAccountsDelete(Request $request, ?User $user, UserWithdrawAccountRepository $waRepository, EntityManagerInterface $em)
    {
        if (is_null($user)) {
            throw $this->createNotFoundException("User not found");
        }

        if ($user != $this->getUser()) {
            throw $this->createAccessDeniedException("Permission denied to view user transactions: {$request->get('id')}");
        }

        $withdrawAccount = $waRepository->findOneBy([
            'user' => $user,
            'id' => $request->get('waId')
        ]);

        if (! $withdrawAccount instanceof UserWithdrawAccount) {
            throw $this->createNotFoundException("Withdraw account not found");
        }

        try {
            $em->remove($withdrawAccount);
            $em->flush();

            return ['success' => true];
        } catch (\Exception $e) {
            throw new ConflictHttpException("Can not delete withdraw account {$request->get('waId')}: " . $e->getMessage());
        }
    }

    /**
     * @Rest\Route("/{id}/withdraw/cryptoaddresses", name="api_user_withdraw_cryptoaddresses", methods={"GET", "POST"})
     * @Rest\View()
     *
     * @QueryParam(name="currency", description="The cryptocurrency code", nullable=false, strict=true)
     *
     * @SWG\Response(response="200", description="List of user withdraw cryptoaddresses",
     *     @SWG\Schema(type="array", @SWG\Items(ref=@Model(type=App\Entity\UserWithdrawCryptoaddress::class)))
     * )
     */
    public function withdrawCryptoAddresses(Request $request, ?User $user, ParamFetcher $paramFetcher, EntityManagerInterface $em,
                                            CurrencyRepository $currencyRepository, MultinodeService $multinodeService, LoggerInterface $logger)
    {
        if (is_null($user)) {
            throw $this->createNotFoundException("User not found");
        }

        if ($user != $this->getUser()) {
            throw $this->createAccessDeniedException("Permission denied to view user withdraw cryptoaddresses: {$request->get('id')}");
        }

        $currencyCode = $paramFetcher->get('currency');

        // POST (Generate new address)
        if (Request::METHOD_POST === $request->getMethod()) {

            $currency = $currencyRepository->findOneByCode($currencyCode);

            if (! $currency instanceof Currency) {
                throw new BadRequestHttpException("Unknown currency code {$currencyCode}");
            }

            $logger->info("Trying to generate new {$currency->getCode()} cryptoaddress for user {$user->getEmail()}");

            $withdrawCryptoaddress = (new UserWithdrawCryptoaddress())
                ->setCurrency($currency)
                ->setUser($user);

            $form = $this->createForm(UserWithdrawCryptoaddressType::class, $withdrawCryptoaddress);

            $form->submit($request->request->all(), false);

            if ($form->isSubmitted() && $form->isValid()) {
                if (!$multinodeService->verifyCryptoAddress($withdrawCryptoaddress->getAddress(), $currency)) {
                    throw new BadRequestHttpException("Invalid address: {$withdrawCryptoaddress->getAddress()}");
                }

                $withdrawCryptoaddress->setUser($user);

                $em->persist($withdrawCryptoaddress);
                $em->flush();

                return ['success' => true, 'cryptoaddress_id' => $withdrawCryptoaddress->getId()];
            }

            return $form;
        }

        return array_values($user->getUserWithdrawCryptoaddresses()->filter(
            function (UserWithdrawCryptoaddress $cryptoaddress) use ($currencyCode) {
                return strtolower($currencyCode) == strtolower($cryptoaddress->getCurrency()->getCode());
            }
        )->toArray());
    }

    /**
     * Delete user's withdraw cryptoaddress
     *
     * @Rest\Route("/{id}/withdraw/cryptoaddresses/{wcId}", name="api_user_withdraw_cryptoaddresses_delete", methods={"DELETE"})
     * @Rest\View()
     *
     * @SWG\Response(response=200, description="Success",
     *     @SWG\Schema(
     *         type="object",
     *         example={"success": true}
     *     )
     * )
     */
    public function withdrawCryptoaddressDelete(Request $request, ?User $user, UserWithdrawCryptoaddressRepository $wcRepository,
                                                EntityManagerInterface $em, LoggerInterface $logger)
    {
        if (is_null($user)) {
            throw $this->createNotFoundException("User not found");
        }

        if ($user != $this->getUser()) {
            throw $this->createAccessDeniedException("Permission denied to delete user withdraw cryptoaddresses: {$request->get('id')}");
        }

        $withdrawCryptoaddress = $wcRepository->findOneBy([
            'user' => $user,
            'id' => $request->get('wcId')
        ]);

        if (! $withdrawCryptoaddress instanceof UserWithdrawCryptoaddress) {
            throw $this->createNotFoundException("Withdraw cryptoaddress not found");
        }

        try {
            $em->remove($withdrawCryptoaddress);
            $em->flush();

            return ['success' => true];
        } catch (\Exception $e) {
            $logger->warning($e);
            throw new ConflictHttpException("Cryptoaddress {$withdrawCryptoaddress->getAddress()} can not be deleted, since it has processed withdrawals in history");
        }
    }

    /**
     * Make an withdrawal to cryptoaddress
     *
     * @Rest\Route("/{id}/withdrawal/cryptoaddress", name="api_user_withdrawal_cryptoaddress_create", methods={"POST"})
     * @Rest\View()
     *
     * @SWG\Parameter(name="payload", description="Withdrawal", required=true, in="body", required=true, @SWG\Schema(ref=@Model(type=App\Entity\CryptoWithdrawal::class)))
     * @SWG\Response(response=200, description="Success",
     *     @SWG\Schema(
     *         type="object",
     *         example={"success": true, "withdrawal_id": 875}
     *     )
     * )
     * @SWG\Response(response=400, description="Validation failed",
     *     @SWG\Schema(
     *         type="object",
     *         example={
     *          "code": 400,
     *          "message": "Validation Failed",
     *          "errors": {}
     *        }
     *     )
     * )
     *
     */
    public function withdrawalCryptoaddressCreate(Request $request, ?User $user, EntityManagerInterface $em,
                                                  SmsServiceInterface $smsService, LoggerInterface $logger)
    {
        // check if user exists
        if (! $user instanceof User) {
            throw $this->createNotFoundException("Not found user with id: {$request->get('id')}");
        }

        // check if user updates his own profile
        if ($user != $this->getUser()) {
            throw $this->createAccessDeniedException("Permission denied to make withdrawal for user: {$request->get('id')}");
        }

        if (is_null($user->getPhone())) {
            throw new BadRequestHttpException("Missing phone number");
        }

        $cryptoWithdrawal = new CryptoWithdrawal();

        $form = $this->createForm(CryptoWithdrawalType::class, $cryptoWithdrawal);

        $form->submit($request->request->all(), false);

        if ($form->isSubmitted() && $form->isValid()) {

            // проверка достаточного баланса для вывода

            $currency = $cryptoWithdrawal->getCryptoaddress()->getCurrency();

            $primaryAccount = $user->getPrimaryAccountByCurrency($currency);
            $holdAccount = $user->getHoldAccountByCurrency($currency);

            $availableAmount = bcadd($primaryAccount->getBalance(), $holdAccount->getBalance(), 8);

            if ($availableAmount < $cryptoWithdrawal->getAmount()) {
                throw new BadRequestHttpException("Not enough balance for withdrawal on the {$currency->getCode()} user account: only {$availableAmount} available ({$holdAccount->getBalance()} on hold), but {$cryptoWithdrawal->getAmount()} needed");
            }

            $cryptoWithdrawal->setUser($user);

            // отправка смс-кода
            {
                $smsCode = (new SmsCode())
                    ->setUser($user)
                    ->setPhone($user->getPhone())
                ;
                $smsService->sendCode($smsCode);
            }

            $em->persist($cryptoWithdrawal);
            $em->flush();

            $logger->info("User {$user->getEmail()} successfully created crypto withdrawal to cryptoaddress {$cryptoWithdrawal->getCryptoaddress()->getAddress()}, amount={$cryptoWithdrawal->getAmount()} {$cryptoWithdrawal->getCryptoaddress()->getCurrency()->getCode()}");

            return ['success' => true, 'withdrawal_id' => $cryptoWithdrawal->getId()];
        }

        return $form;
    }

    /**
     * Make an withdrawal to fiat account
     *
     * @Rest\Route("/{id}/withdrawal/account", name="api_user_withdrawal_account_create", methods={"POST"})
     * @Rest\View()
     *
     * @SWG\Parameter(name="payload", description="Withdrawal", required=true, in="body", required=true, @SWG\Schema(ref=@Model(type=App\Entity\FiatWithdrawal::class)))
     * @SWG\Response(response=200, description="Success",
     *     @SWG\Schema(
     *         type="object",
     *         example={"success": true, "withdrawal_id": 875}
     *     )
     * )
     * @SWG\Response(response=400, description="Validation failed",
     *     @SWG\Schema(
     *         type="object",
     *         example={
     *          "code": 400,
     *          "message": "Validation Failed",
     *          "errors": {}
     *        }
     *     )
     * )
     *
     */
    public function withdrawalAccountCreate(Request $request, ?User $user, EntityManagerInterface $em,
                                            SmsServiceInterface $smsService, MonolithServiceInterface $monolithService)
    {
        // check if user exists
        if (! $user instanceof User) {
            throw $this->createNotFoundException("Not found user with id: {$request->get('id')}");
        }

        // check if user updates his own profile
        if ($user != $this->getUser()) {
            throw $this->createAccessDeniedException("Permission denied to make withdrawal for user: {$request->get('id')}");
        }

        if (is_null($user->getPhone())) {
            throw new BadRequestHttpException("Missing phone number");
        }

        $fiatWithdrawal = new FiatWithdrawal();

        $form = $this->createForm(FiatWithdrawalType::class, $fiatWithdrawal);

        $form->submit($request->request->all(), false);

        if ($form->isSubmitted() && $form->isValid()) {
            $fiatWithdrawal->setUser($user);

            $em->persist($fiatWithdrawal);
            $em->flush();

            try {
                $confirmationToken = $monolithService->paymentInitialization($fiatWithdrawal);
            } catch (\Exception $e) {
                throw new BadRequestHttpException($e->getMessage());
            }

            return [
                'success' => true,
                'withdrawal_id' => $fiatWithdrawal->getId(),
                'confirmation_token' => $confirmationToken,
            ];
        }

        return $form;
    }

    /**
     * Make an withdrawal to fiat account
     *
     * @Rest\Route("/{id}/transfer", name="api_user_transfer", methods={"POST"})
     * @Rest\View()
     *
     * @SWG\Parameter(name="payload", description="Transfer to another user", required=true, in="body", required=true, @SWG\Schema(ref=@Model(type=App\Entity\Transfer::class)))
     * @SWG\Response(response=200, description="Success",
     *     @SWG\Schema(
     *         type="object",
     *         example={"success": true, "transfer_id": 578}
     *     )
     * )
     * @SWG\Response(response=400, description="Validation failed",
     *     @SWG\Schema(
     *         type="object",
     *         example={
     *          "code": 400,
     *          "message": "Validation Failed",
     *          "errors": {}
     *        }
     *     )
     * )
     *
     */
    public function transfer(Request $request, User $user, EntityManagerInterface $em, TransferService $transferService)
    {
        // check if user exists
        if (! $user instanceof User) {
            throw $this->createNotFoundException("Not found user with id: {$request->get('id')}");
        }

        // check if user transfers from his own account
        if ($user != $this->getUser()) {
            throw $this->createAccessDeniedException("Permission denied to make transfer for user: {$request->get('id')}");
        }

        $transfer = new Transfer();

        $form = $this->createForm(TransferType::class, $transfer);

        $form->submit($request->request->all(), false);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($transfer->getAccountFrom()->getUser() != $user) {
                throw new BadRequestHttpException("User {$user->getEmail()} does not have account {$transfer->getAccountFrom()->getId()}");
            }

            if ($transfer->getAccountFrom()->getCurrency() != $transfer->getAccountTo()->getCurrency()) {
                throw new BadRequestHttpException(sprintf("Can not convert %s to %s on transfer",
                    $transfer->getAccountFrom()->getCurrency()->getCode(),
                    $transfer->getAccountTo()->getCurrency()->getCode()
                ));
            }

            if ($transfer->getAccountFrom()->getType() != UserAccount::TYPE_PRIMARY
                || $transfer->getAccountFrom()->getCurrency()->getType() != Currency::TYPE_CRYPTO
            ) {
                throw new BadRequestHttpException("Source account is not crypto primary");
            }


            if ($transfer->getAccountTo()->getType() != UserAccount::TYPE_PRIMARY
                || $transfer->getAccountTo()->getCurrency()->getType() != Currency::TYPE_CRYPTO
            ) {
                throw new BadRequestHttpException("Destination account is not crypto primary");
            }

            $em->persist($transfer);
            $em->flush();

            $transferService->processTransfer($transfer);

            return [
                'success' => true,
                'transfer_id' => $transfer->getId(),
            ];
        }

        return $form;
    }

    /**
     * Check sent SMS-code for withdrawal
     *
     * @Rest\Route("/{id}/check_sms_code/withdrawal-account/{waId}", name="api_user_checksmscode_withdrawal_account", methods={"POST"})
     * @Rest\Route("/{id}/check_sms_code/withdrawal-cryptoaddress/{wcId}", name="api_user_checksmscode_withdrawal_cryptoaddress", methods={"POST"})
     * @Rest\View()
     *
     * @SWG\Parameter(name="payload", description="User's phone", required=true, in="body", required=true,
     *     @SWG\Schema(
     *          type="object",
     *          example={"code": "1010"}
     *     )
     * )
     * @SWG\Response(response=200, description="Success",
     *     @SWG\Schema(
     *         type="object",
     *         example={"success": true}
     *     )
     * )
     * @SWG\Response(response=400, description="Validation failed",
     *     @SWG\Schema(
     *         type="object",
     *         example={
     *          "code": 400,
     *          "message": "Invalid SMS code"
     *        }
     *     )
     * )
     *
     */
    public function checkWithdrawalSmsCode(Request $request, ?User $user, SmsServiceInterface $smsService, EntityManagerInterface $em,
                                 ?int $waId, ?int $wcId, MultinodeService $multinodeService, MonolithServiceInterface $monolithService,
                                AMLInspectionInterface $amlInspectionService
    )
    {
        // check if user exists
        if (! $user instanceof User) {
            throw $this->createNotFoundException("Not found user with id: {$request->get('id')}");
        }

        // check if user updates his own profile
        if ($user != $this->getUser()) {
            throw $this->createAccessDeniedException("Permission denied to check withdrawal for user: {$user->getEmail()}");
        }

        $form = $this->createForm(WithdrawalSmsCodeCheckType::class, new SmsCode());

        $form->submit($request->request->all(), false);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($waId) {
                // подтверждение снятия фиата (FiatWithdrawal)
                $confirmationToken = $request->get('confirmation_token');
                if (!$confirmationToken) {
                    throw new BadRequestHttpException("Missing confirmation_token");
                }

                $fiatWithdrawal = $em->getRepository(FiatWithdrawal::class)
                    ->findOneBy([
                        'id' => $waId,
                        'user' => $user
                    ]);
                if (!$fiatWithdrawal) {
                    throw $this->createNotFoundException("FiatWithdrawal with id=$waId for user {$user->getEmail()} not found");
                }

                try {
                    $monolithService->paymentConfirmation($fiatWithdrawal, $confirmationToken, $form->getData()->getCode());
                    $fiatWithdrawal->setConfirmedAt(new \DateTime());
                } catch (IntegrationException $e) {
                    throw new BadRequestHttpException($e->getMessage());
                } catch (\Exception $e) {
                    throw $e;
                }
            } elseif ($wcId) {
                // подтверждение снятия крипты (CryptoWithdrawal)

                $smsCode = (new SmsCode())
                    ->setCode($form->getData()->getCode())
                    ->setPhone($user->getPhone())
                    ->setUser($user);

                if (!$smsService->checkCode($smsCode)) {
                    throw new BadRequestHttpException("Invalid sms code");
                }

                $cryptoWithdrawal = $em->getRepository(CryptoWithdrawal::class)->findOneBy([
                    'id' => $wcId,
                    'user' => $user
                ]);
                if (! $cryptoWithdrawal instanceof CryptoWithdrawal) {
                    throw $this->createNotFoundException("CryptoWithdrawal with id=$wcId for user {$user->getEmail()} not found");
                }
                $cryptoWithdrawal->setConfirmedAt(new \DateTime());

                $em->flush();

                // todo: инициировать АМЛ проверку
                $amlInspectionService->checkCryptoWithdrawal($cryptoWithdrawal);

                // это делаем после успешной АМЛ проверки: (переносим со 2 шага на 3й)
                $multinodeService->withdraw($cryptoWithdrawal);
            } else {
                throw new BadRequestHttpException("Missing withdrawal id");
            }

            $em->flush();

            return ['success' => true];
        }

        return $form;
    }

    /**
     * Get withdrawal info
     *
     * @Rest\Route("/{id}/withdrawal/info", name="api_user_withdrawal_info", methods={"GET"})
     * @Rest\View()
     *
     * @QueryParam(name="currency", description="The currency code", nullable=false, strict=true)
     *
     * @SWG\Response(response=200, description="Withdrawal information",
     *     @SWG\Schema(type="array", @SWG\Items(ref=@Model(type=App\Entity\Model\Info\WithdrawalInfo::class))))
     * )
     *
     */
    public function withdrawalInfo(Request $request, ?User $user, ParamFetcher $paramFetcher,
                                   CurrencyRepository $currencyRepository, AccountService $accountService)
    {
        $maximum = 100000;

        // check if user exists
        if (! $user instanceof User) {
            throw $this->createNotFoundException("Not found user with id: {$request->get('id')}");
        }

        // check if user updates his own profile
        if ($user != $this->getUser()) {
            throw $this->createAccessDeniedException("Permission denied to make withdrawal for user: {$request->get('id')}");
        }

        /** @var Currency $currency */
        $currency = $currencyRepository->findOneByCode($paramFetcher->get('currency'));

        if (! $currency) {
            throw new BadRequestHttpException("Invalid currency {$paramFetcher->get('currency')}");
        }

        $withdrawalInfo = new WithdrawalInfo();

        $balance = bcadd(
            $user->getPrimaryAccountByCurrency($currency)->getBalance(),
            $user->getHoldAccountByCurrency($currency)->getBalance(),
            8
        );
        $withdrawalInfo->balance = $balance;

        $withdrawalInfo->minimum = Currency::TYPE_FIAT == $currency->getType()
            ? '0.01'
            : '0.0000001';

        $withdrawalInfo->fee = 0.1;
        $withdrawalInfo->inOrders = -1 * $user->getHoldAccountByCurrency($currency)->getBalance();
        $withdrawalInfo->maximum = $balance > $maximum
            ? $maximum
            : $balance;

        return $withdrawalInfo;
    }

    /**
     * Resends SMS-code for withdrawal
     *
     * @Rest\Route("/{id}/resend_sms_code/withdrawal-account/{waId}", name="api_user_resendsmscode_withdrawal_account", methods={"POST"})
     * @Rest\Route("/{id}/resend_sms_code/withdrawal-cryptoaddress/{wcId}", name="api_user_resendsmscode_withdrawal_cryptoaddress", methods={"POST"})
     * @Rest\View()
     *
     * @SWG\Response(response=200, description="Success",
     *     @SWG\Schema(
     *         type="object",
     *         example={"success": true}
     *     )
     * )
     *
     */
    public function resendWithdrawalSmsCode(Request $request, ?User $user, SmsServiceInterface $smsService, EntityManagerInterface $em,
                                           ?int $waId, ?int $wcId, MultinodeService $multinodeService, LoggerInterface $logger)
    {
        // check if user exists
        if (! $user instanceof User) {
            throw $this->createNotFoundException("Not found user with id: {$request->get('id')}");
        }

        // check if user updates his own profile
        if ($user != $this->getUser()) {
            throw $this->createAccessDeniedException("Permission denied to resend withdrawal code for user: {$request->get('id')}");
        }

        // todo: check withdrawal is open
        // todo: assign sms-code to checking current withdrawal, not others

        $smsCode = (new SmsCode())
            ->setUser($user)
            ->setPhone($user->getPhone())
        ;
        $smsService->sendCode($smsCode);

        $logger->info("User {$user->getEmail()} resent new sms-code to {$user->getPhone()}");

        return ['success' => true];

        /*$form = $this->createForm(WithdrawalSmsCodeCheckType::class, new SmsCode());

        $form->submit($request->request->all(), false);

        if ($form->isSubmitted() && $form->isValid()) {
            $smsCode = (new SmsCode())
                ->setCode($form->getData()->getCode())
                ->setPhone($user->getPhone())
                ->setUser($user);

            if (!$smsService->checkCode($smsCode)) {
                throw new BadRequestHttpException("Invalid sms code");
            }

            if ($waId) {
                // подтверждение снятия фиата (FiatWithdrawal)
                $fiatWithdrawal = $em->getRepository(FiatWithdrawal::class)->find($waId);
                $fiatWithdrawal->setConfirmedAt(new \DateTime());
            } elseif ($wcId) {
                // подтверждение снятия крипты (CryptoWithdrawal)
                $cryptoWithdrawal = $em->getRepository(CryptoWithdrawal::class)->find($wcId);
                $cryptoWithdrawal->setConfirmedAt(new \DateTime());
                $multinodeService->withdraw($cryptoWithdrawal);
            } else {
                throw new BadRequestHttpException("Missing withdrawal id");
            }

            $em->flush();

            return ['success' => true];
        }*/

        return $form;
    }

}
