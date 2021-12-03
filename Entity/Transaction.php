<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use JMS\Serializer\Annotation as JMS;


/**
 * @ORM\Entity(repositoryClass="App\Repository\TransactionRepository")
 *
 * @JMS\ExclusionPolicy("all")
 */
class Transaction
{
    const TYPE_DEPOSIT           = 1;
    const TYPE_WITHDRAW          = 2;
    const TYPE_FILL_ORDER_BUY    = 3;
    const TYPE_FILL_ORDER_SELL   = 4;
    const TYPE_FILL_FEE          = 5;
    const TYPE_HOLD              = 6;
    const TYPE_UNHOLD            = 7;
    const TYPE_FEE               = 8;
    const TYPE_EXCHANGE_BUY      = 9;
    const TYPE_EXCHANGE_SELL     = 10;
    const TYPE_TRANSFER_OUTGOING = 11;
    const TYPE_TRANSFER_INCOMING = 12;

    public static $typesText = [
        self::TYPE_DEPOSIT           => 'Deposit',
        self::TYPE_WITHDRAW          => 'Withdraw',
        self::TYPE_FILL_ORDER_BUY    => 'Buy',
        self::TYPE_FILL_ORDER_SELL   => 'Sell',
        self::TYPE_FILL_FEE          => 'Fee',
        self::TYPE_HOLD              => 'Hold',
        self::TYPE_UNHOLD            => 'Unhold',
        self::TYPE_FEE               => 'Fee',
        self::TYPE_EXCHANGE_BUY      => 'Exchange:buy',
        self::TYPE_EXCHANGE_SELL     => 'Exchange:sell',
        self::TYPE_TRANSFER_OUTGOING => 'Outgoing transfer',
        self::TYPE_TRANSFER_INCOMING => 'Incoming transfer',
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     * @JMS\Expose
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Gedmo\Timestampable(on="create")
     *
     * @JMS\Expose
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\UserAccount", inversedBy="transactions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $userAccount;

    /**
     * @ORM\Column(type="smallint")
     *
     * @JMS\Expose
     */
    private $type;

    /**
     * @ORM\Column(type="decimal", precision=19, scale=8)
     *
     * @JMS\Expose
     * @JMS\Type("string")
     */
    private $amount;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @JMS\Expose
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Order", inversedBy="transactions")
     * @ORM\JoinColumn(onDelete="SET NULL"))
     */
    private $order;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ExchangeOrder", inversedBy="transactions")
     * @ORM\JoinColumn(onDelete="SET NULL"))
     */
    private $exchangeOrder;

    /**
     * @JMS\VirtualProperty
     * @JMS\Expose
     */
    public function getAsset()
    {
        return $this->getUserAccount()->getCurrency()->getCode();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUserAccount(): ?UserAccount
    {
        return $this->userAccount;
    }

    public function setUserAccount(UserAccount $userAccount): self
    {
        $this->userAccount = $userAccount;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function getTypeText(): string
    {
        return static::$typesText[$this->getType()];
    }

    public function setType(int $type): self
    {
        if (!array_key_exists($type, static::$typesText)) {
            throw new BadRequestHttpException(sprintf("Invalid transaction type: %s. Only supported: %s",
                    $type,
                    implode(',', array_keys(static::$typesText)))
            );
        }

        $this->type = $type;

        return $this;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): self
    {
        $this->order = $order;

        return $this;
    }

    public function getExchangeOrder(): ?ExchangeOrder
    {
        return $this->exchangeOrder;
    }

    public function setExchangeOrder(?ExchangeOrder $exchangeOrder): self
    {
        $this->exchangeOrder = $exchangeOrder;

        return $this;
    }

}
