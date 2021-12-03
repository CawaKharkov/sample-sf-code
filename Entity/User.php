<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use JMS\Serializer\Annotation as JMS;


/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 * @JMS\ExclusionPolicy("all")
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 */
class User implements UserInterface, EquatableInterface
{
    const STATUS_INITIAL                     = 0;
    const STATUS_PHONE_SENT                  = 1;
    const STATUS_PHONE_VERIFIED              = 2;
    const STATUS_SENT_TO_GETID               = 4;
    const STATUS_FORM_COMPLETED              = 5;
    const STATUS_GETID_ERROR                 = 6;
    const STATUS_GETID_NOT_MATCHED_WITH_FORM = 7;
    const STATUS_SENT_TO_MONOLITH            = 8;

    /**
     * NONE - either there are no registration for the phone, or there are more than 1 unfinished registration and they have different statuses (new regsitration will suspend all previous ones)
    REGISTERED - phone number already persists in Monolith, and, likely, the client with such phone is regsitered in bank (some clients are added to Monolith manually, without registration).
    PROCESSING - there is an active regsitration for the phone. Monolith processes it at the moment of the response (e.g. Monolith submitted data to OpenApi and waits for the response)
    REJECTED - one of OpenApi requests (request to submit regsitration form, to submit additional data or to order card) has been failed.
    CARD_ORDERING - Monolith processes card ordering for the client.
    ADDITIONAL_INFO - bank has requested additional info (address proof, proof of wealth and/or employment type)
    FAILED_VALIDATION - the match of IdScan journey and client registration form has been failed. The reasons could be Monolith failure to retrieve IdScan journey (unexisting journey, or upload to wrong IdScan server) or mismatch of data (see Monoliith algorythm of journey validation)
    CANCELED - bank has cancelled the regsitration by submitted form.
    CLIENT_EXISTS - client with such data (name, surname, identity document number, etc.) already exists in the bank, but he is registered for different phone.
     */

    const STATUS_MONOLITH_NONE              = 0;
    const STATUS_MONOLITH_REGISTERED        = 1;
    const STATUS_MONOLITH_PROCESSING        = 2;
    const STATUS_MONOLITH_REJECTED          = 3;
    const STATUS_MONOLITH_CARD_ORDERING     = 4;
    const STATUS_MONOLITH_ADDITIONAL_INFO   = 5;
    const STATUS_MONOLITH_FAILED_VALIDATION = 6;
    const STATUS_MONOLITH_CANCELED          = 7;
    const STATUS_MONOLITH_CLIENT_EXISTS     = 8;

    public static $monolithStatusesText = [
        self::STATUS_MONOLITH_NONE              => 'NONE',
        self::STATUS_MONOLITH_REGISTERED        => 'REGISTERED',
        self::STATUS_MONOLITH_PROCESSING        => 'PROCESSING',
        self::STATUS_MONOLITH_REJECTED          => 'REJECTED',
        self::STATUS_MONOLITH_CARD_ORDERING     => 'CARD_ORDERING',
        self::STATUS_MONOLITH_ADDITIONAL_INFO   => 'ADDITIONAL_INFO',
        self::STATUS_MONOLITH_FAILED_VALIDATION => 'FAILED_VALIDATION',
        self::STATUS_MONOLITH_CANCELED          => 'CANCELED',
        self::STATUS_MONOLITH_CLIENT_EXISTS     => 'CLIENT_EXISTS',
    ];


    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     *
     * @JMS\Expose
     * @JMS\Groups({"list", "profile"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     *
     * @JMS\Expose
     * @JMS\Groups({"list", "profile"})
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @var string The bcrypt-hash of the password for referral
     * @ORM\Column(type="string", nullable=true)
     */
    private $referralPasswordHash;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserAccount", mappedBy="user")
     */
    private $userAccounts;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     *
     * @JMS\Expose
     * @JMS\Groups({"list", "profile"})
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     *
     * @JMS\Expose
     * @JMS\Groups({"list", "profile"})
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $middleName;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Order", mappedBy="user")
     */
    private $orders;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     *
     * @JMS\Expose
     * @JMS\Groups({"list", "profile"})
     */
    private $timezone;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @JMS\Expose
     * @JMS\Groups({"profile"})
     *
     */
    private $twoFactorEmail;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @JMS\Expose
     * @JMS\Groups({"profile"})
     */
    private $twoFactorGoogle;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserSecretQuestionAnswer", mappedBy="user")
     *
     * @JMS\Groups({"profile"})
     * @JMS\Type("array<App\Entity\UserSecretQuestionAnswer>")
     */
    private $userSecretQuestionAnswers;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserWhiteIp", mappedBy="user")
     *
     */
    private $userWhiteIps;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserPaymentMethod", mappedBy="user")
     *
     */
    private $userPaymentMethods;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     *
     */
    private $addressCity;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     *
     */
    private $addressZip;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     *
     * @JMS\Expose
     * @JMS\Groups({"profile"})
     *
     */
    private $idNumber;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     *
     */
    private $idCountry;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     *
     */
    private $idIssuedBy;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserWatchlist", mappedBy="user")
     *
     * @JMS\Expose
     * @JMS\Groups({"profile"})
     */
    private $watchlists;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @JMS\Expose
     * @JMS\Groups({"profile"})
     */
    private $isVerified = false;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     *
     * @JMS\Expose
     * @JMS\Groups({"profile"})
     */
    private $phone;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     *
     * @JMS\Expose
     * @JMS\Groups({"profile"})
     */
    private $gender;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="date", nullable=true)
     *
     * @JMS\Expose
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @JMS\Groups({"profile"})
     */
    private $birthDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @JMS\Expose
     * @JMS\Groups({"profile"})
     */
    private $birthPlace;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     *
     * @JMS\Expose
     * @JMS\Groups({"profile"})
     */
    private $birthCountry;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     *
     * @JMS\Expose
     * @JMS\Groups({"profile"})
     */
    private $cardName;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     *
     * @JMS\Expose
     * @JMS\Groups({"profile"})
     */
    private $residenceCountry;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     *
     * @JMS\Expose
     * @JMS\Groups({"profile"})
     */
    private $residenceIdentificationNumber;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     *
     * @JMS\Expose
     * @JMS\Groups({"profile"})
     */
    private $residenceStreet;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     *
     * @JMS\Expose
     * @JMS\Groups({"profile"})
     */
    private $residenceHouse;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     *
     * @JMS\Expose
     * @JMS\Groups({"profile"})
     */
    private $residenceApartment;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     *
     * @JMS\Expose
     * @JMS\Groups({"profile"})
     */
    private $residenceCity;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     *
     * @JMS\Expose
     * @JMS\Groups({"profile"})
     */
    private $residenceProvince;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     *
     * @JMS\Expose
     * @JMS\Groups({"profile"})
     */
    private $residencePostalCode;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     *
     * @JMS\Expose
     * @JMS\Groups({"profile"})
     */
    private $deliveryCountry;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     *
     * @JMS\Expose
     * @JMS\Groups({"profile"})
     */
    private $deliveryCity;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     *
     * @JMS\Expose
     * @JMS\Groups({"profile"})
     */
    private $deliveryIndex;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @JMS\Expose
     * @JMS\Groups({"profile"})
     */
    private $deliveryAddress;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     *
     * @JMS\Expose
     * @JMS\Groups({"profile"})
     */
    private $deliveryRecipient;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     *
     * @JMS\Expose
     * @JMS\Groups({"profile"})
     */
    private $deliveryOption;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     *
     * @JMS\Expose
     * @JMS\Groups({"profile"})
     */
    private $identityCitizenship;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     *
     * @JMS\Expose
     * @JMS\Groups({"profile"})
     */
    private $identityType;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     *
     * @JMS\Expose
     * @JMS\Groups({"profile"})
     */
    private $identityNumber;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="date", nullable=true)
     *
     * @JMS\Expose
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @JMS\Groups({"profile"})
     */
    private $identityIssueDate;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="date", nullable=true)
     *
     * @JMS\Expose
     * @JMS\Type("DateTime<'Y-m-d'>")
     * @JMS\Groups({"profile"})
     */
    private $identityExpiryDate;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     *
     * @JMS\Expose
     * @JMS\Groups({"profile"})
     */
    private $identityIssuer;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @JMS\Expose
     * @JMS\Groups({"profile"})
     */
    private $additionalPoliticPerson;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @JMS\Expose
     * @JMS\Groups({"profile"})
     */
    private $additionalPoliticFamily;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     *
     * @JMS\Expose
     * @JMS\Groups({"profile"})
     */
    private $additionalPromoCode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @JMS\Groups({"profile"})
     * @JMS\Expose
     * @JMS\Type("string")
     * @JMS\Accessor(getter="getImageDocumentWebpath")
     */
    private $imageDocument;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @JMS\Groups({"profile"})
     * @JMS\Expose
     * @JMS\Type("string")
     * @JMS\Accessor(getter="getImageSelfieWebpath")
     */
    private $imageSelfie;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     *
     * @JMS\Groups({"profile"})
     * @JMS\Expose
     */
    private $isVerificationSent;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SmsCode", mappedBy="user")
     */
    private $smsCodes;

    /**
     * @ORM\OneToMany(targetEntity="UserDepositCryptoaddress", mappedBy="user")
     */
    private $userDepositCryptoaddresses;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $uasToken;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $uasTokenExpirationAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $uasSessId;

    /**
     * @ORM\OneToMany(targetEntity="UserWithdrawAccount", mappedBy="user")
     */
    private $userWithdrawAccounts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserWithdrawCryptoaddress", mappedBy="user")
     */
    private $userWithdrawCryptoaddresses;

    /**
     * @ORM\OneToMany(targetEntity="CryptoWithdrawal", mappedBy="user")
     */
    private $cryptoWithdrawals;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\FiatWithdrawal", mappedBy="user")
     */
    private $fiatWithdrawals;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserDepositMethod", mappedBy="user")
     */
    private $userDepositMethods;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isBot = false;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $getidData = [];

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $notMatchedGetidData = null;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $sentToGetid = false;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     *
     * @JMS\Expose
     * @JMS\Groups({"profile"})
     */
    private $status = self::STATUS_INITIAL;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     *
     * @JMS\Expose
     * @JMS\Groups({"profile"})
     */
    private $monolithStatus;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $completed = false;


    public function __construct()
    {
        $this->userAccounts = new ArrayCollection();
        $this->orders = new ArrayCollection();
        $this->userSecretQuestionAnswers = new ArrayCollection();
        $this->userWhiteIps = new ArrayCollection();
        $this->userPaymentMethods = new ArrayCollection();
        $this->watchlists = new ArrayCollection();
        $this->smsCodes = new ArrayCollection();
        $this->userDepositCryptoaddresses = new ArrayCollection();
        $this->userWithdrawAccounts = new ArrayCollection();
        $this->userWithdrawCryptoaddresses = new ArrayCollection();
        $this->cryptoWithdrawals = new ArrayCollection();
        $this->fiatWithdrawals = new ArrayCollection();
        $this->userDepositMethods = new ArrayCollection();
    }

    /**
     * @ORM\PreRemove
     */
    public function removeImages()
    {
        if (!is_null($this->getImageDocument())) {
            unlink($this->getImageDocument());
            $this->setImageDocument(null);
        }

        if (!is_null($this->getImageSelfie())) {
            unlink($this->getImageSelfie());
            $this->setImageSelfie(null);
        }

        $dir = __DIR__ . "/../../public/uploads/{$this->getId()}";
        if (is_dir($dir) && !glob($dir . '/*')) {
            rmdir($dir);
        }
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @param int|null $type
     * @return UserAccount[]|Collection
     */
    public function getUserAccounts(int $type = null)
    {
        if ($type) {
            return $this->userAccounts->filter(function (UserAccount $account) use ($type) {
                return $type == $account->getType();
            })->getValues();
        }

        return $this->userAccounts;
    }

    public function addAccount(UserAccount $account): self
    {
        if (!$this->userAccounts->contains($account)) {
            $this->userAccounts[] = $account;
            $account->setUser($this);
        }

        return $this;
    }

    public function removeAccount(UserAccount $account): self
    {
        if ($this->userAccounts->contains($account)) {
            $this->userAccounts->removeElement($account);
            // set the owning side to null (unless already changed)
            if ($account->getUser() === $this) {
                $account->setUser(null);
            }
        }

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getMiddleName(): ?string
    {
        return $this->middleName;
    }

    public function setMiddleName(string $middleName): self
    {
        $this->middleName = $middleName;

        return $this;
    }

    /**
     * @return Collection|Order[]
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): self
    {
        if (!$this->orders->contains($order)) {
            $this->orders[] = $order;
            $order->setUser($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): self
    {
        if ($this->orders->contains($order)) {
            $this->orders->removeElement($order);
            // set the owning side to null (unless already changed)
            if ($order->getUser() === $this) {
                $order->setUser(null);
            }
        }

        return $this;
    }

    public function getTimezone(): ?int
    {
        return $this->timezone;
    }

    public function setTimezone(?int $timezone): self
    {
        $this->timezone = $timezone;

        return $this;
    }

    public function getTwoFactorEmail(): ?bool
    {
        return $this->twoFactorEmail;
    }

    public function setTwoFactorEmail(?bool $twoFactorEmail): self
    {
        $this->twoFactorEmail = $twoFactorEmail;

        return $this;
    }

    public function getTwoFactorGoogle(): ?bool
    {
        return $this->twoFactorGoogle;
    }

    public function setTwoFactorGoogle(?bool $twoFactorGoogle): self
    {
        $this->twoFactorGoogle = $twoFactorGoogle;

        return $this;
    }

    /**
     * @return Collection|UserSecretQuestionAnswer[]
     */
    public function getUserSecretQuestionAnswers(): Collection
    {
        return $this->userSecretQuestionAnswers;
    }

    public function addUserSecretQuestionAnswer(UserSecretQuestionAnswer $userSecretQuestionAnswer): self
    {
        if (!$this->userSecretQuestionAnswers->contains($userSecretQuestionAnswer)) {
            $this->userSecretQuestionAnswers[] = $userSecretQuestionAnswer;
            $userSecretQuestionAnswer->setUser($this);
        }

        return $this;
    }

    public function removeUserSecretQuestionAnswer(UserSecretQuestionAnswer $userSecretQuestionAnswer): self
    {
        if ($this->userSecretQuestionAnswers->contains($userSecretQuestionAnswer)) {
            $this->userSecretQuestionAnswers->removeElement($userSecretQuestionAnswer);
            // set the owning side to null (unless already changed)
            if ($userSecretQuestionAnswer->getUser() === $this) {
                $userSecretQuestionAnswer->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserWhiteIp[]
     */
    public function getUserWhiteIps(): Collection
    {
        return $this->userWhiteIps;
    }

    public function addUserWhiteIp(UserWhiteIp $userWhiteIp): self
    {
        if (!$this->userWhiteIps->contains($userWhiteIp)) {
            $this->userWhiteIps[] = $userWhiteIp;
            $userWhiteIp->setUser($this);
        }

        return $this;
    }

    public function removeUserWhiteIp(UserWhiteIp $userWhiteIp): self
    {
        if ($this->userWhiteIps->contains($userWhiteIp)) {
            $this->userWhiteIps->removeElement($userWhiteIp);
            // set the owning side to null (unless already changed)
            if ($userWhiteIp->getUser() === $this) {
                $userWhiteIp->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserPaymentMethod[]
     */
    public function getUserPaymentMethods(): Collection
    {
        return $this->userPaymentMethods;
    }

    public function addUserPaymentMethod(UserPaymentMethod $userPaymentMethod): self
    {
        if (!$this->userPaymentMethods->contains($userPaymentMethod)) {
            $this->userPaymentMethods[] = $userPaymentMethod;
            $userPaymentMethod->setUser($this);
        }

        return $this;
    }

    public function removeUserPaymentMethod(UserPaymentMethod $userPaymentMethod): self
    {
        if ($this->userPaymentMethods->contains($userPaymentMethod)) {
            $this->userPaymentMethods->removeElement($userPaymentMethod);
            // set the owning side to null (unless already changed)
            if ($userPaymentMethod->getUser() === $this) {
                $userPaymentMethod->setUser(null);
            }
        }

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getAddressCity(): ?string
    {
        return $this->addressCity;
    }

    public function setAddressCity(?string $addressCity): self
    {
        $this->addressCity = $addressCity;

        return $this;
    }

    public function getAddressZip(): ?string
    {
        return $this->addressZip;
    }

    public function setAddressZip(?string $addressZip): self
    {
        $this->addressZip = $addressZip;

        return $this;
    }

    public function getIdNumber(): ?string
    {
        return $this->idNumber;
    }

    public function setIdNumber(?string $idNumber): self
    {
        $this->idNumber = $idNumber;

        return $this;
    }

    public function getIdCountry(): ?string
    {
        return $this->idCountry;
    }

    public function setIdCountry(?string $idCountry): self
    {
        $this->idCountry = $idCountry;

        return $this;
    }

    public function getIdIssuedBy(): ?string
    {
        return $this->idIssuedBy;
    }

    public function setIdIssuedBy(?string $idIssuedBy): self
    {
        $this->idIssuedBy = $idIssuedBy;

        return $this;
    }

    public function getName()
    {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }

    /**
     * @return Collection|UserWatchlist[]
     */
    public function getWatchlists(): Collection
    {
        return $this->watchlists;
    }

    public function addWatchlist(UserWatchlist $watchlist): self
    {
        if (!$this->watchlists->contains($watchlist)) {
            $this->watchlists[] = $watchlist;
            $watchlist->setUser($this);
        }

        return $this;
    }

    public function removeWatchlist(UserWatchlist $watchlist): self
    {
        if ($this->watchlists->contains($watchlist)) {
            $this->watchlists->removeElement($watchlist);
            // set the owning side to null (unless already changed)
            if ($watchlist->getUser() === $this) {
                $watchlist->setUser(null);
            }
        }

        return $this;
    }

    public function getIsVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(?bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @param int|null $type
     * @return ArrayCollection
     */
    public function getTransactions(int $type = null)
    {
        $all = new ArrayCollection();

        foreach ($this->getUserAccounts($type) as $account) {
            if ($account->getType() == UserAccount::TYPE_HOLD) {
                // do not display transactions on hold accounts
                continue;
            }
            foreach ($account->getTransactions() as $transaction) {
                $all->add($transaction);
            }
        }

        return $all;
    }

    public function getAccountsProfit(string $period): string
    {
        // todo: real rates profit

        $profit = 0;

        foreach ($this->getUserAccounts() as $account) {
            $profit += $account->getChange();
        }

        return $profit;
    }

    public function getPrimaryAccountByCurrency(Currency $currency) : ?UserAccount
    {
        return $this->getUserAccounts()->filter(
            function (UserAccount $account) use ($currency) {
                return $account->getType() == UserAccount::TYPE_PRIMARY
                    && $account->getCurrency() == $currency;
            }
        )->first();
    }

    public function getHoldAccountByCurrency(Currency $holdCurrency) : ?UserAccount
    {
        return $account = $this->getUserAccounts()->filter(
            function (UserAccount $userAccount) use ($holdCurrency) {
                return $userAccount->getType() == UserAccount::TYPE_HOLD
                    && $userAccount->getCurrency() == $holdCurrency;
            }
        )->first();
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getGender(): ?int
    {
        return $this->gender;
    }

    public function setGender(?int $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function getBirthDateAsString(): ?string
    {
        return $this->birthDate ? $this->birthDate->format('Y-m-d') : null;
    }

    /**
     * @param \DateTimeInterface|string $birthDate
     * @return User
     */
    public function setBirthDate($birthDate): self
    {
        if (is_string($birthDate)) {
            $birthDate = new \DateTime($birthDate);
        }
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getBirthPlace(): ?string
    {
        return $this->birthPlace;
    }

    public function setBirthPlace(?string $birthPlace): self
    {
        $this->birthPlace = $birthPlace;

        return $this;
    }

    public function getCardName(): ?string
    {
        return $this->cardName;
    }

    public function setCardName(?string $cardName): self
    {
        $this->cardName = $cardName;

        return $this;
    }

    public function getResidenceCountry(): ?string
    {
        return $this->residenceCountry;
    }

    public function setResidenceCountry(?string $residenceCountry): self
    {
        $this->residenceCountry = $residenceCountry;

        return $this;
    }

    public function getResidenceStreet(): ?string
    {
        return $this->residenceStreet;
    }

    public function setResidenceStreet(?string $residenceStreet): self
    {
        $this->residenceStreet = $residenceStreet;

        return $this;
    }

    public function getResidenceHouse(): ?string
    {
        return $this->residenceHouse;
    }

    public function setResidenceHouse(?string $residenceHouse): self
    {
        $this->residenceHouse = $residenceHouse;

        return $this;
    }

    public function getResidenceApartment(): ?string
    {
        return $this->residenceApartment;
    }

    public function setResidenceApartment(?string $residenceApartment): self
    {
        $this->residenceApartment = $residenceApartment;

        return $this;
    }

    public function getResidenceCity(): ?string
    {
        return $this->residenceCity;
    }

    public function setResidenceCity(?string $residenceCity): self
    {
        $this->residenceCity = $residenceCity;

        return $this;
    }

    public function getResidenceProvince(): ?string
    {
        return $this->residenceProvince;
    }

    public function setResidenceProvince(?string $residenceProvince): self
    {
        $this->residenceProvince = $residenceProvince;

        return $this;
    }

    public function getResidencePostalCode(): ?string
    {
        return $this->residencePostalCode;
    }

    public function setResidencePostalCode(?string $residencePostalCode): self
    {
        $this->residencePostalCode = $residencePostalCode;

        return $this;
    }

    public function getDeliveryCountry(): ?string
    {
        return $this->deliveryCountry;
    }

    public function setDeliveryCountry(?string $deliveryCountry): self
    {
        $this->deliveryCountry = $deliveryCountry;

        return $this;
    }

    public function getDeliveryCity(): ?string
    {
        return $this->deliveryCity;
    }

    public function setDeliveryCity(?string $deliveryCity): self
    {
        $this->deliveryCity = $deliveryCity;

        return $this;
    }

    public function getDeliveryIndex(): ?string
    {
        return $this->deliveryIndex;
    }

    public function setDeliveryIndex(?string $deliveryIndex): self
    {
        $this->deliveryIndex = $deliveryIndex;

        return $this;
    }

    public function getDeliveryAddress(): ?string
    {
        return $this->deliveryAddress;
    }

    public function setDeliveryAddress(?string $deliveryAddress): self
    {
        $this->deliveryAddress = $deliveryAddress;

        return $this;
    }

    public function getDeliveryRecipient(): ?string
    {
        return $this->deliveryRecipient;
    }

    public function setDeliveryRecipient(?string $deliveryRecipient): self
    {
        $this->deliveryRecipient = $deliveryRecipient;

        return $this;
    }

    public function getDeliveryOption(): ?int
    {
        return $this->deliveryOption;
    }

    public function setDeliveryOption(?int $deliveryOption): self
    {
        $this->deliveryOption = $deliveryOption;

        return $this;
    }

    public function getIdentityCitizenship(): ?string
    {
        return $this->identityCitizenship;
    }

    public function setIdentityCitizenship(?string $identityCitizenship): self
    {
        $this->identityCitizenship = $identityCitizenship;

        return $this;
    }

    public function getIdentityType(): ?int
    {
        return $this->identityType;
    }

    public function setIdentityType(?int $identityType): self
    {
        $this->identityType = $identityType;

        return $this;
    }

    public function getIdentityNumber(): ?string
    {
        return $this->identityNumber;
    }

    public function setIdentityNumber(?string $identityNumber): self
    {
        $this->identityNumber = $identityNumber;

        return $this;
    }

    public function getIdentityIssueDate(): ?\DateTimeInterface
    {
        return $this->identityIssueDate;
    }

    public function getIdentityIssueDateAsString(): ?string
    {
        return $this->identityIssueDate ? $this->identityIssueDate->format('Y-m-d') : null;
    }

    /**
     * @param \DateTimeInterface|string $identityIssueDate
     * @return User
     */
    public function setIdentityIssueDate($identityIssueDate): self
    {
        if (is_string($identityIssueDate)) {
            $identityIssueDate = new \DateTime($identityIssueDate);
        }
        $this->identityIssueDate = $identityIssueDate;

        return $this;
    }

    public function getIdentityExpiryDate(): ?\DateTimeInterface
    {
        return $this->identityExpiryDate;
    }

    /**
     * @param \DateTimeInterface|string $identityExpiryDate
     * @return User
     */
    public function setIdentityExpiryDate($identityExpiryDate): self
    {
        if (is_string($identityExpiryDate)) {
            $identityExpiryDate = new \DateTime($identityExpiryDate);
        }
        $this->identityExpiryDate = $identityExpiryDate;

        return $this;
    }

    public function getIdentityIssuer(): ?string
    {
        return $this->identityIssuer;
    }

    public function setIdentityIssuer(?string $identityIssuer): self
    {
        $this->identityIssuer = $identityIssuer;

        return $this;
    }

    public function getAdditionalPoliticPerson(): ?bool
    {
        return $this->additionalPoliticPerson;
    }

    public function setAdditionalPoliticPerson(?bool $additionalPoliticPerson): self
    {
        $this->additionalPoliticPerson = $additionalPoliticPerson;

        return $this;
    }

    public function getAdditionalPoliticFamily(): ?bool
    {
        return $this->additionalPoliticFamily;
    }

    public function setAdditionalPoliticFamily(?bool $additionalPoliticFamily): self
    {
        $this->additionalPoliticFamily = $additionalPoliticFamily;

        return $this;
    }

    public function getBirthCountry(): ?string
    {
        return $this->birthCountry;
    }

    public function setBirthCountry(?string $birthCountry): self
    {
        $this->birthCountry = $birthCountry;

        return $this;
    }

    public function getResidenceIdentificationNumber(): ?string
    {
        return $this->residenceIdentificationNumber;
    }

    public function setResidenceIdentificationNumber(?string $residenceIdentificationNumber): self
    {
        $this->residenceIdentificationNumber = $residenceIdentificationNumber;

        return $this;
    }

    public function getImageDocument(): ?string
    {
        return $this->imageDocument;
    }

    public function setImageDocument(?string $imageDocument): self
    {
        $this->imageDocument = $imageDocument;

        return $this;
    }

    public function getImageSelfie(): ?string
    {
        return $this->imageSelfie;
    }

    public function setImageSelfie(?string $imageSelfie): self
    {
        $this->imageSelfie = $imageSelfie;

        return $this;
    }

    public function getImageDocumentWebpath()
    {
        if (empty($this->getImageDocument())) {
            return '';
        }

        // todo configure upload path
        preg_match('/(\/uploads\/.*)/', $this->getImageDocument(), $matches);
        return $matches[1];
    }

    public function getImageSelfieWebpath()
    {
        if (empty($this->getImageSelfie())) {
            return '';
        }

        // todo configure upload path
        preg_match('/(\/uploads\/.*)/', $this->getImageSelfie(), $matches);
        return $matches[1];
    }

    public function getAdditionalPromoCode(): ?string
    {
        return $this->additionalPromoCode;
    }

    public function setAdditionalPromoCode(?string $additionalPromoCode): self
    {
        $this->additionalPromoCode = $additionalPromoCode;

        return $this;
    }

    public function getIsVerificationSent(): ?bool
    {
        return $this->isVerificationSent;
    }

    public function setIsVerificationSent(?bool $isVerificationSent): self
    {
        $this->isVerificationSent = $isVerificationSent;

        return $this;
    }

    /**
     * @return Collection|SmsCode[]
     */
    public function getSmsCodes(): Collection
    {
        return $this->smsCodes;
    }

    public function addSmsCode(SmsCode $smsCode): self
    {
        if (!$this->smsCodes->contains($smsCode)) {
            $this->smsCodes[] = $smsCode;
            $smsCode->setUser($this);
        }

        return $this;
    }

    public function removeSmsCode(SmsCode $smsCode): self
    {
        if ($this->smsCodes->contains($smsCode)) {
            $this->smsCodes->removeElement($smsCode);
            // set the owning side to null (unless already changed)
            if ($smsCode->getUser() === $this) {
                $smsCode->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserDepositCryptoaddress[]
     */
    public function getUserDepositCryptoaddresses(): Collection
    {
        return $this->userDepositCryptoaddresses;
    }

    public function addDepositCryptoAddress(UserDepositCryptoaddress $cryptoAddress): self
    {
        if (!$this->userDepositCryptoaddresses->contains($cryptoAddress)) {
            $this->userDepositCryptoaddresses[] = $cryptoAddress;
            $cryptoAddress->setUser($this);
        }

        return $this;
    }

    public function removeDepositCryptoAddress(UserDepositCryptoaddress $cryptoAddress): self
    {
        if ($this->userDepositCryptoaddresses->contains($cryptoAddress)) {
            $this->userDepositCryptoaddresses->removeElement($cryptoAddress);
            // set the owning side to null (unless already changed)
            if ($cryptoAddress->getUser() === $this) {
                $cryptoAddress->setUser(null);
            }
        }

        return $this;
    }

    public function getUasToken(): ?string
    {
        return $this->uasToken;
    }

    public function setUasToken(?string $uasToken): self
    {
        $this->uasToken = $uasToken;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getUasTokenExpirationAt(): ?\DateTimeInterface
    {
        return $this->uasTokenExpirationAt;
    }

    /**
     * @return null|string
     */
    public function getUasTokenExpirationAtAsString(): ?string
    {
        return is_null($this->getUasTokenExpirationAt())
            ? null
            : $this->getUasTokenExpirationAt()->format('Y-m-d H:i:s');
    }

    public function setUasTokenExpirationAt(?\DateTimeInterface $uasTokenExpirationAt): self
    {
        $this->uasTokenExpirationAt = $uasTokenExpirationAt;

        return $this;
    }

    public function getUasSessId(): ?string
    {
        return $this->uasSessId;
    }

    public function setUasSessId(?string $uasSessId): self
    {
        $this->uasSessId = $uasSessId;

        return $this;
    }

    /**
     * @return Collection|UserWithdrawAccount[]
     */
    public function getUserWithdrawAccounts(): Collection
    {
        return $this->userWithdrawAccounts;
    }

    public function addWithdrawAccount(UserWithdrawAccount $withdrawAccount): self
    {
        if (!$this->userWithdrawAccounts->contains($withdrawAccount)) {
            $this->userWithdrawAccounts[] = $withdrawAccount;
            $withdrawAccount->setUser($this);
        }

        return $this;
    }

    public function removeWithdrawAccount(UserWithdrawAccount $withdrawAccount): self
    {
        if ($this->userWithdrawAccounts->contains($withdrawAccount)) {
            $this->userWithdrawAccounts->removeElement($withdrawAccount);
            // set the owning side to null (unless already changed)
            if ($withdrawAccount->getUser() === $this) {
                $withdrawAccount->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserWithdrawCryptoaddress[]
     */
    public function getUserWithdrawCryptoaddresses(): Collection
    {
        return $this->userWithdrawCryptoaddresses;
    }

    public function addWithdrawCryptoaddress(UserWithdrawCryptoaddress $withdrawCryptoaddress): self
    {
        if (!$this->userWithdrawCryptoaddresses->contains($withdrawCryptoaddress)) {
            $this->userWithdrawCryptoaddresses[] = $withdrawCryptoaddress;
            $withdrawCryptoaddress->setUser($this);
        }

        return $this;
    }

    public function removeWithdrawCryptoaddress(UserWithdrawCryptoaddress $withdrawCryptoaddress): self
    {
        if ($this->userWithdrawCryptoaddresses->contains($withdrawCryptoaddress)) {
            $this->userWithdrawCryptoaddresses->removeElement($withdrawCryptoaddress);
            // set the owning side to null (unless already changed)
            if ($withdrawCryptoaddress->getUser() === $this) {
                $withdrawCryptoaddress->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|CryptoWithdrawal[]
     */
    public function getCryptoWithdrawals(): Collection
    {
        return $this->cryptoWithdrawals;
    }

    public function addCryptoWithdrawal(CryptoWithdrawal $withdrawal): self
    {
        if (!$this->cryptoWithdrawals->contains($withdrawal)) {
            $this->cryptoWithdrawals[] = $withdrawal;
            $withdrawal->setUser($this);
        }

        return $this;
    }

    public function removeCryptoWithdrawal(CryptoWithdrawal $withdrawal): self
    {
        if ($this->cryptoWithdrawals->contains($withdrawal)) {
            $this->cryptoWithdrawals->removeElement($withdrawal);
            // set the owning side to null (unless already changed)
            if ($withdrawal->getUser() === $this) {
                $withdrawal->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|FiatWithdrawal[]
     */
    public function getFiatWithdrawals(): Collection
    {
        return $this->fiatWithdrawals;
    }

    public function addFiatWithdrawal(FiatWithdrawal $fiatWithdrawal): self
    {
        if (!$this->fiatWithdrawals->contains($fiatWithdrawal)) {
            $this->fiatWithdrawals[] = $fiatWithdrawal;
            $fiatWithdrawal->setUser($this);
        }

        return $this;
    }

    public function removeFiatWithdrawal(FiatWithdrawal $fiatWithdrawal): self
    {
        if ($this->fiatWithdrawals->contains($fiatWithdrawal)) {
            $this->fiatWithdrawals->removeElement($fiatWithdrawal);
            // set the owning side to null (unless already changed)
            if ($fiatWithdrawal->getUser() === $this) {
                $fiatWithdrawal->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserDepositMethod[]
     */
    public function getUserDepositMethods(): Collection
    {
        return $this->userDepositMethods;
    }

    public function addUserDepositMethod(UserDepositMethod $userDepositMethod): self
    {
        if (!$this->userDepositMethods->contains($userDepositMethod)) {
            $this->userDepositMethods[] = $userDepositMethod;
            $userDepositMethod->setUser($this);
        }

        return $this;
    }

    public function removeUserDepositMethod(UserDepositMethod $userDepositMethod): self
    {
        if ($this->userDepositMethods->contains($userDepositMethod)) {
            $this->userDepositMethods->removeElement($userDepositMethod);
            // set the owning side to null (unless already changed)
            if ($userDepositMethod->getUser() === $this) {
                $userDepositMethod->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsBot()
    {
        return $this->isBot;
    }

    /**
     * @param mixed $isBot
     */
    public function setIsBot($isBot)
    {
        $this->isBot = $isBot;
    }


    /**
     * The equality comparison should neither be done by referential equality
     * nor by comparing identities (i.e. getId() === getId()).
     *
     * However, you do not need to compare every attribute, but only those that
     * are relevant for assessing whether re-authentication is required.
     *
     * @return bool
     */
    public function isEqualTo(UserInterface $user)
    {
        if ($this->email !== $user->getUsername()) {
            return false;
        }

        if ($this->password !== $user->getPassword()) {
            return false;
        }

        return true;
    }

    public function getRolesAsString()
    {
        return implode(', ', $this->getRoles());
    }

    /**
     * @return mixed
     */
    public function getGetidData()
    {
        return $this->getidData;
    }

    /**
     * @return mixed
     */
    public function getGetidDataAsString()
    {
        return \json_encode($this->getidData);
    }

    /**
     * @param mixed $getidData
     */
    public function setGetidData($getidData)
    {
        $this->getidData = $getidData;
    }

    /**
     * @return mixed
     */
    public function getNotMatchedGetidData()
    {
        return $this->notMatchedGetidData;
    }

    /**
     * @return mixed
     */
    public function getNotMatchedGetidDataAsString()
    {
        return \json_encode($this->notMatchedGetidData);
    }

    /**
     * @param mixed $notMatchedGetidData
     */
    public function setNotMatchedGetidData($notMatchedGetidData)
    {
        $this->notMatchedGetidData = $notMatchedGetidData;
    }

    /**
     * @return bool
     */
    public function isGetidMatched(): bool
    {
        return empty($this->getNotMatchedGetidData()) && !empty($this->getGetidData());
    }

    /**
     * @return mixed
     */
    public function getSentToGetid()
    {
        return $this->sentToGetid;
    }

    /**
     * @param mixed $sentToGetid
     */
    public function setSentToGetid($sentToGetid)
    {
        $this->sentToGetid = $sentToGetid;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getCompleted()
    {
        return $this->completed;
    }

    /**
     * @param mixed $completed
     */
    public function setCompleted($completed)
    {
        $this->completed = $completed;
    }

    public function getGenderText()
    {
        if (is_null($this->getGender())) {
            return null;
        }
        return $this->getGender() == 1 ? 'male' : 'female';
    }

    /**
     * @return mixed
     */
    public function getMonolithStatus()
    {
        return $this->monolithStatus;
    }

    /**
     * @param mixed $monolithStatus
     */
    public function setMonolithStatus($monolithStatus)
    {
        $this->monolithStatus = $monolithStatus;
    }

    /**
     * @return string
     */
    public function getFullname()
    {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }

    /**
     * @return string|null
     */
    public function getReferralPasswordHash(): ?string
    {
        return $this->referralPasswordHash;
    }

    /**
     * @param string $referralPasswordHash
     */
    public function setReferralPasswordHash(string $referralPasswordHash)
    {
        $this->referralPasswordHash = $referralPasswordHash;
    }

    /**
     * @return bool
     *
     * @JMS\Expose
     * @JMS\VirtualProperty
     * @JMS\Groups({"profile"})
     * @JMS\SerializedName("from_referral")
     */
    public function fromReferral(): bool
    {
        return !empty($this->getReferralPasswordHash());
    }

}
