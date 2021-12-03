<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AMLInspectionRepository")
 */
class AMLInspection
{

    const STATUS_SCHEDULED = 0;
    const STATUS_ALLOWED   = 1;
    const STATUS_DECLINED  = 2;

    const TYPE_CRYPTO_WITHDRAWAL = 1;
    const TYPE_CRYPTO_DEPOSIT = 2;
    const TYPE_FIAT_WITHDRAWAL = 3;
    const TYPE_FIAT_DEPOSIT = 4;


    public static $statusesText = [
        self::STATUS_SCHEDULED => 'Scheduled',
        self::STATUS_ALLOWED => 'Allowed',
        self::STATUS_DECLINED => 'Declined',
    ];

    public static $typesText = [
        '' => '',
        self::TYPE_CRYPTO_WITHDRAWAL => 'Crypto Withdrawal',
        self::TYPE_CRYPTO_DEPOSIT => 'Crypto Deposit',
        self::TYPE_FIAT_WITHDRAWAL => 'Fiat Withdrawal',
        self::TYPE_FIAT_DEPOSIT => 'Fiat Deposit',
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\FiatWithdrawal", inversedBy="amlInspections")
     */
    private $fiatWithdrawal;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CryptoWithdrawal", inversedBy="amlInspections")
     */
    private $cryptoWithdrawal;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CryptoDeposit", inversedBy="amlInspections")
     */
    private $cryptoDeposit;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\DepositTransaction", inversedBy="amlInspections")
     */
    private $depositTransaction;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastInspectedAt;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(type="integer")
     */
    private $type;

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

    public function getFiatWithdrawal(): ?FiatWithdrawal
    {
        return $this->fiatWithdrawal;
    }

    public function setFiatWithdrawal(?FiatWithdrawal $fiatWithdrawal): self
    {
        $this->fiatWithdrawal = $fiatWithdrawal;

        return $this;
    }

    public function getCryptoWithdrawal(): ?CryptoWithdrawal
    {
        return $this->cryptoWithdrawal;
    }

    public function setCryptoWithdrawal(?CryptoWithdrawal $cryptoWithdrawal): self
    {
        $this->cryptoWithdrawal = $cryptoWithdrawal;

        return $this;
    }

    public function getCryptoDeposit(): ?CryptoDeposit
    {
        return $this->cryptoDeposit;
    }

    public function setCryptoDeposit(?CryptoDeposit $cryptoDeposit): self
    {
        $this->cryptoDeposit = $cryptoDeposit;

        return $this;
    }

    public function getDepositTransaction(): ?DepositTransaction
    {
        return $this->depositTransaction;
    }

    public function setDepositTransaction(?DepositTransaction $depositTransaction): self
    {
        $this->depositTransaction = $depositTransaction;

        return $this;
    }

    public function getLastInspectedAt(): ?\DateTimeInterface
    {
        return $this->lastInspectedAt;
    }

    public function setLastInspectedAt(?\DateTimeInterface $lastInspectedAt): self
    {
        $this->lastInspectedAt = $lastInspectedAt;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatusText(): string
    {
        return !is_null($this->getStatus())
            ? self::$statusesText[$this->getStatus()]
            : '';
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getTypeText(): string
    {
        return self::$typesText[$this->getType()];
    }

    /**
     * @return string|null
     */
    public function getAmount()
    {
        if ($this->getCryptoWithdrawal()) {
            return $this->getCryptoWithdrawal()->getAmount();
        } elseif ($this->getCryptoDeposit()) {
            return $this->getCryptoDeposit()->getAmount();
        } elseif ($this->getFiatWithdrawal()) {
            return $this->getFiatWithdrawal()->getAmount();
        } elseif ($this->getDepositTransaction()) {
            return $this->getDepositTransaction()->getAmount();
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getRecipient()
    {
        if ($this->getCryptoWithdrawal()) {
            return $this->getCryptoWithdrawal()->getCryptoaddress()->getAddress();
        } elseif ($this->getCryptoDeposit()) {
            return $this->getCryptoDeposit()->getCryptoaddress()->getAddress();
        } elseif ($this->getFiatWithdrawal()) {
            return $this->getFiatWithdrawal()->getAccount()->getNameOnAccount();
        } elseif ($this->getDepositTransaction()) {
            return $this->getDepositTransaction()->getUserDepositMethod()->getReference();
        }

        return null;
    }
}
