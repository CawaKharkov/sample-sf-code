<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass="App\Repository\ExchangeOrderRepository")
 * @ORM\Table(indexes={@ORM\Index(name="IDX_USER_STATUS", columns={"user_id", "status"})})
 *
 * @JMS\ExclusionPolicy("all")
 */
class ExchangeOrder
{
    const TYPE_SELL   = 1;
    const TYPE_BUY    = 2;

    const STATUS_OPEN                  = 1;
    const STATUS_CLOSED_FILLED         = 2;
    const STATUS_CLOSED_PARTIAL_FILLED = 3;
    const STATUS_CLOSED_CANCELLED      = 4;

    const CORE_STATE_CREATED        = 1;
    const CORE_STATE_CREATION_ERROR = 2;
    const CORE_STATE_DELETED        = 3;
    const CORE_STATE_DELETE_ERROR   = 4;

    const SIDE_BUYER = 1;
    const SIDE_SELLER = 2;

    public static $typesText = [
        self::TYPE_SELL  => 'Exchange: sell',
        self::TYPE_BUY   => 'Exchange: buy',
    ];

    public static $statusesText = [
        self::STATUS_OPEN                  => 'Open',
        self::STATUS_CLOSED_FILLED         => 'Filled',
        self::STATUS_CLOSED_PARTIAL_FILLED => 'Partial filled',
        self::STATUS_CLOSED_CANCELLED      => 'Cancelled',
    ];

    public static $coreStatesText = [
        self::CORE_STATE_CREATED        => 'Created',
        self::CORE_STATE_CREATION_ERROR => 'Creation error',
        self::CORE_STATE_DELETED        => 'Deleted',
        self::CORE_STATE_DELETE_ERROR   => 'Delete error',
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     *
     * @JMS\Expose
     */
    private $id;


    /**
     * @ORM\Column(type="decimal", precision=19, scale=8)
     *
     * @JMS\Expose
     * @JMS\Type("string")
     *
     * @Assert\NotBlank
     */
    private $amount;

    /**
     * @ORM\Column(type="decimal", precision=19, scale=8, nullable=true)
     *
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $amountBuy;

    /**
     * @ORM\Column(type="decimal", precision=19, scale=8, nullable=true)
     *
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $amountPay;

    /**
     * @ORM\Column(type="decimal", precision=19, scale=8, nullable=true)
     *
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $amountSell;

    /**
     * @ORM\Column(type="decimal", precision=19, scale=8, nullable=true)
     *
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $amountGet;

    /**
     * @ORM\Column(type="decimal", precision=19, scale=8)
     *
     * @JMS\Expose
     * @JMS\Type("string")
     * @JMS\Accessor(getter="getPrice")
     *
     * @Assert\NotBlank
     */
    private $price;

    /**
     * @ORM\Column(type="smallint")
     *
     * @JMS\Expose
     *
     * @Assert\NotBlank
     */
    private $type;

    /**
     * @ORM\Column(type="smallint")
     *
     * @JMS\Expose
     *
     * @Assert\NotBlank
     */
    private $status = 1;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     *
     * @JMS\Expose
     */
    private $coreState;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="orders")
     * @ORM\JoinColumn(nullable=false)
     *
     * @JMS\Expose
     * @JMS\Accessor(getter="getUserId")
     * @JMS\Type("string")
     */
    private $user;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Gedmo\Timestampable(on="create")
     *
     * @JMS\Expose
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

    /**
     * @var ExchangeDirection
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ExchangeDirection", inversedBy="exchangeOrders")
     * @ORM\JoinColumn(nullable=false)
     *
     * @JMS\Expose
     * @JMS\Accessor(getter="getExchangeDirectionId")
     * @JMS\Type("integer")
     */
    private $direction;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @JMS\Expose
     */
    private $closedAt;


    public function __construct()
    {
        $this->transactions = new ArrayCollection();
        $this->operations = new ArrayCollection();
    }

    public function setId(string $id)
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        if (!array_key_exists($type, static::$typesText)) {
            throw new BadRequestHttpException(sprintf("Invalid exchange order type: %s. Only supported: %s",
                $type,
                implode(',', array_keys(static::$typesText)))
            );
        }

        $this->type = $type;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getUserId(): string
    {
        return $this->getUser()->getId();
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getTypeText(): string
    {
        return static::$typesText[$this->getType()];
    }

    public function getDirection(): ?ExchangeDirection
    {
        return $this->direction;
    }

    public function getExchangeDirectionId(): int
    {
        return $this->getDirection()->getId();
    }

    public function setDirection(ExchangeDirection $direction): self
    {
        $this->direction = $direction;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function getStatusText(): string
    {
        return static::$statusesText[$this->getStatus()];
    }

    public function setStatus(int $status): self
    {
        if (!array_key_exists($status, static::$statusesText)) {
            throw new BadRequestHttpException(sprintf("Invalid exchange order status: %s. Only supported: %s",
                    $status,
                    implode(',', array_keys(static::$statusesText)))
            );
        }

        $this->status = $status;

        return $this;
    }

    public function getClosedAt(): ?\DateTimeInterface
    {
        return $this->closedAt;
    }

    public function setClosedAt(?\DateTimeInterface $closedAt): self
    {
        $this->closedAt = $closedAt;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCoreState()
    {
        return $this->coreState;
    }

    /**
     * @param mixed $coreState
     */
    public function setCoreState($coreState)
    {
        if (!array_key_exists($coreState, static::$coreStatesText)) {
            throw new BadRequestHttpException(sprintf("Invalid order core state: %s. Only supported: %s",
                    $coreState,
                    implode(',', array_keys(static::$coreStatesText)))
            );
        }

        $this->coreState = $coreState;
    }

    public function getSide() : int
    {
        $map = [
            self::TYPE_BUY  => self::SIDE_BUYER,
            self::TYPE_SELL => self::SIDE_SELLER,
        ];

        if (!array_key_exists($this->getType(), $map)) {
            throw new \LogicException("Exchange order type {$this->getTypeText()} not supported yet");
        }

        return $map[$this->getType()];
    }

    public function getCurrencyIncrease(): Currency
    {
        return $this->getSide() == ExchangeOrder::SIDE_BUYER
            ? $this->getDirection()->getCurrencyBase()
            : $this->getDirection()->getCurrencyQuote();
    }

    public function getCurrencyDecrease(): Currency
    {
        return $this->getSide() == ExchangeOrder::SIDE_BUYER
            ? $this->getDirection()->getCurrencyQuote()
            : $this->getDirection()->getCurrencyBase();
    }

    public function getCurrencyIncreaseForSide(int $side): Currency
    {
        return $side == ExchangeOrder::SIDE_BUYER
            ? $this->getDirection()->getCurrencyBase()
            : $this->getDirection()->getCurrencyQuote();
    }

    public function getCurrencyDecreaseForSide(int $side): Currency
    {
        return $side == ExchangeOrder::SIDE_BUYER
            ? $this->getDirection()->getCurrencyBase()
            : $this->getDirection()->getCurrencyQuote();
    }

    /**
     * @return mixed
     */
    public function getAmountBuy()
    {
        return $this->amountBuy;
    }

    /**
     * @param mixed $amountBuy
     */
    public function setAmountBuy($amountBuy): self
    {
        $this->amountBuy = $amountBuy;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAmountPay()
    {
        return $this->amountPay;
    }

    /**
     * @param mixed $amountPay
     */
    public function setAmountPay($amountPay): self
    {
        $this->amountPay = $amountPay;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAmountSell()
    {
        return $this->amountSell;
    }

    /**
     * @param mixed $amountSell
     */
    public function setAmountSell($amountSell): self
    {
        $this->amountSell = $amountSell;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAmountGet()
    {
        return $this->amountGet;
    }

    /**
     * @param mixed $amountGet
     */
    public function setAmountGet($amountGet): self
    {
        $this->amountGet = $amountGet;

        return $this;
    }

}
