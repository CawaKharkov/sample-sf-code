<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


/**
 * @ORM\Entity(repositoryClass="App\Repository\OrderRepository")
 * @ORM\Table(name="`order`", indexes={@ORM\Index(name="IDX_USER_STATUS", columns={"user_id", "status"})})
 *
 * @JMS\ExclusionPolicy("all")
 */
class Order
{
    const TYPE_BY_MARKET_SELL   = 1;
    const TYPE_BY_MARKET_BUY    = 2;
    const TYPE_BY_LIMIT_SELL    = 3;
    const TYPE_BY_LIMIT_BUY     = 4;
    const TYPE_STOP_LOSS_SELL   = 5;
    const TYPE_STOP_LOSS_BUY    = 6;
    const TYPE_TAKE_PROFIT_SELL = 7;
    const TYPE_TAKE_PROFIT_BUY  = 8;

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
        self::TYPE_BY_MARKET_SELL => 'By market: sell',
        self::TYPE_BY_MARKET_BUY  => 'By market: buy',
        self::TYPE_BY_LIMIT_SELL  => 'By limit: sell',
        self::TYPE_BY_LIMIT_BUY   => 'By limit: buy',
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
     */
    private $amount;

    /**
     * @ORM\Column(type="decimal", precision=19, scale=8, nullable=true)
     *
     * @JMS\Expose
     * @JMS\Type("string")
     * @JMS\SerializedName("amount_filled")
     */
    private $amountFilled;

    /**
     * @ORM\Column(type="decimal", precision=19, scale=8, nullable=true)
     *
     * @JMS\Expose
     * @JMS\Type("string")
     * @JMS\Accessor(getter="getPriceOrFilled")
     */
    private $price;

    /**
     * @ORM\Column(type="decimal", precision=19, scale=8, nullable=true)
     *
     * @JMS\Expose
     * @JMS\Type("string")
     * @JMS\SerializedName("price_filled")
     */
    private $priceFilled;

    /**
     * @ORM\Column(type="smallint")
     *
     * @JMS\Expose
     */
    private $type;

    /**
     * @ORM\Column(type="smallint")
     *
     * @JMS\Expose
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Direction", inversedBy="orders")
     * @ORM\JoinColumn(nullable=false)
     *
     * @JMS\Expose
     * @JMS\Accessor(getter="getDirectionId")
     * @JMS\Type("integer")
     */
    private $direction;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @JMS\Expose
     */
    private $closedAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Transaction", mappedBy="order")
     */
    private $transactions;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\OrderFillingOperation", mappedBy="order")
     */
    private $operations;

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

    public function getAmountFilled(): ?string
    {
        return $this->amountFilled;
    }

    public function setAmountFilled(string $amountFilled): self
    {
        $this->amountFilled = $amountFilled;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function getPriceOrFilled(): ?string
    {
        return in_array($this->getType(), [Order::TYPE_BY_MARKET_BUY, Order::TYPE_BY_MARKET_SELL])
            ? $this->getPriceFilled()
            : $this->getPrice();
    }


    public function setPrice(string $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getPriceFilled(): ?string
    {
        return $this->priceFilled;
    }

    public function setPriceFilled(string $priceFilled): self
    {
        $this->priceFilled = $priceFilled;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        if (!array_key_exists($type, static::$typesText)) {
            throw new BadRequestHttpException(sprintf("Invalid order type: %s. Only supported: %s",
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

    public function getDirection(): ?Direction
    {
        return $this->direction;
    }

    public function getDirectionId(): int
    {
        return $this->getDirection()->getId();
    }

    public function setDirection(?Direction $direction): self
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
            throw new BadRequestHttpException(sprintf("Invalid order status: %s. Only supported: %s",
                    $status,
                    implode(',', array_keys(static::$statusesText)))
            );
        }

        $this->status = $status;

        if (in_array($status, [
            self::STATUS_CLOSED_FILLED,
            self::STATUS_CLOSED_CANCELLED,
            self::STATUS_CLOSED_PARTIAL_FILLED,
        ])) {
            $this->setClosedAt(new \DateTime());
        }

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

    /**
     * @return Collection|Transaction[]
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): self
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions[] = $transaction;
            $transaction->setOrder($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): self
    {
        if ($this->transactions->contains($transaction)) {
            $this->transactions->removeElement($transaction);
            // set the owning side to null (unless already changed)
            if ($transaction->getOrder() === $this) {
                $transaction->setOrder(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|OrderFillingOperation[]
     */
    public function getOperations(): Collection
    {
        return $this->operations;
    }

    public function addOperation(OrderFillingOperation $operation): self
    {
        if (!$this->operations->contains($operation)) {
            $this->operations[] = $operation;
            $operation->setOrder($this);
        }

        return $this;
    }

    public function removeOperation(OrderFillingOperation $operation): self
    {
        if ($this->operations->contains($operation)) {
            $this->operations->removeElement($operation);
            // set the owning side to null (unless already changed)
            if ($operation->getOrder() === $this) {
                $operation->setOrder(null);
            }
        }

        return $this;
    }

    public function getSide() : int
    {
        $map = [
            self::TYPE_BY_LIMIT_BUY  => self::SIDE_BUYER,
            self::TYPE_BY_MARKET_BUY => self::SIDE_BUYER,
            self::TYPE_BY_LIMIT_SELL => self::SIDE_SELLER,
            self::TYPE_BY_MARKET_SELL => self::SIDE_SELLER,
        ];

        if (!array_key_exists($this->getType(), $map)) {
            throw new \LogicException("Order type {$this->getTypeText()} not supported yet");
        }

        return $map[$this->getType()];
    }

    /**
     * @param OrderFillingOperation $operation
     * @return $this
     */
    public function fill(OrderFillingOperation $operation)
    {
        if (!in_array($this->getStatus(), [self::STATUS_OPEN])) {
            throw new \LogicException("Unable to fill order {$this->getId()} in status " . $this->getStatusText());
        }

        // Amount
        if ($operation->getAmount() == $this->getAmount()) {
            $this->setStatus(self::STATUS_CLOSED_FILLED);
        } elseif ($operation->getAmount() < $this->getAmount()) {
            $amountLeft = bcsub($this->getAmount(), $operation->getAmount(), 8);
            $this->setAmount($amountLeft);
            //$this->setStatus(self::STATUS_PARTIAL_FILLED);
        } else {
            throw new \LogicException("Unable to fill order {$this->getId()} amount {$this->getAmount()} with {$operation->getAmount()}");
        }

        // PriceFilled
        if (!empty($operation->getPrice())) {
            $this->setPriceFilled($operation->getPrice());
        } else {
            // calculate average price*amount
        }

        return $this;
    }

    public function getCurrencyIncrease(): Currency
    {
        return $this->getSide() == Order::SIDE_BUYER
            ? $this->getDirection()->getCurrencyBase()
            : $this->getDirection()->getCurrencyQuote();
    }

    public function getCurrencyDecrease(): Currency
    {
        return $this->getSide() == Order::SIDE_BUYER
            ? $this->getDirection()->getCurrencyQuote()
            : $this->getDirection()->getCurrencyBase();
    }
}
