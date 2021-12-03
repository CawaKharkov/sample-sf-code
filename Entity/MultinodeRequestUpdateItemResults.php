<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MultinodeRequestUpdateItemResultsRepository")
 */
class MultinodeRequestUpdateItemResults
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $chain;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $updateState;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $category;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $addressTo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $addressFrom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $txid;

    /**
     * @ORM\Column(type="decimal", precision=19, scale=8, nullable=true)
     */
    private $fee;

    /**
     * @ORM\Column(type="decimal", precision=19, scale=8)
     */
    private $amount;

    /**
     * @ORM\Column(type="integer")
     */
    private $confirmations;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $txDbId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $isCoinbaseFor;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $domainName;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MultinodeRequestUpdateItem", inversedBy="txUpdateResults")
     * @ORM\JoinColumn(nullable=false)
     */
    private $multinodeRequestUpdateItem;

    /**
     * @ORM\Column(type="decimal", precision=19, scale=8, nullable=true)
     */
    private $receiveFee;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $receiveFeeCurrency;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $state;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $confirmedAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cryptoBrokerId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $coinbaseTxid;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $__v;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $_id;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChain(): ?string
    {
        return $this->chain;
    }

    public function setChain(string $chain): self
    {
        $this->chain = $chain;

        return $this;
    }

    public function getUpdateState(): ?string
    {
        return $this->updateState;
    }

    public function setUpdateState(string $updateState): self
    {
        $this->updateState = $updateState;

        return $this;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function setUser(string $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getAddressTo(): ?string
    {
        return $this->addressTo;
    }

    public function setAddressTo(string $addressTo): self
    {
        $this->addressTo = $addressTo;

        return $this;
    }

    public function getAddressFrom(): ?string
    {
        return $this->addressFrom;
    }

    public function setAddressFrom(string $addressFrom): self
    {
        $this->addressFrom = $addressFrom;

        return $this;
    }

    public function getTxid(): ?string
    {
        return $this->txid;
    }

    public function setTxid(string $txid): self
    {
        $this->txid = $txid;

        return $this;
    }

    public function getFee(): ?string
    {
        return $this->fee;
    }

    public function setFee(string $fee): self
    {
        $this->fee = $fee;

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

    public function getConfirmations(): ?int
    {
        return $this->confirmations;
    }

    public function setConfirmations(int $confirmations): self
    {
        $this->confirmations = $confirmations;

        return $this;
    }

    public function getTxDbId(): ?string
    {
        return $this->txDbId;
    }

    public function setTxDbId(string $txDbId): self
    {
        $this->txDbId = $txDbId;

        return $this;
    }

    public function getIsCoinbaseFor(): ?string
    {
        return $this->isCoinbaseFor;
    }

    public function setIsCoinbaseFor(string $isCoinbaseFor): self
    {
        $this->isCoinbaseFor = $isCoinbaseFor;

        return $this;
    }

    public function getDomainName(): ?string
    {
        return $this->domainName;
    }

    public function setDomainName(string $domainName): self
    {
        $this->domainName = $domainName;

        return $this;
    }

    public function getMultinodeRequestUpdateItem(): ?MultinodeRequestUpdateItem
    {
        return $this->multinodeRequestUpdateItem;
    }

    public function setMultinodeRequestUpdateItem(?MultinodeRequestUpdateItem $multinodeRequestUpdateItem): self
    {
        $this->multinodeRequestUpdateItem = $multinodeRequestUpdateItem;

        return $this;
    }

    public function getReceiveFee(): ?string
    {
        return $this->receiveFee;
    }

    public function setReceiveFee(?string $receiveFee): self
    {
        $this->receiveFee = $receiveFee;

        return $this;
    }

    public function getReceiveFeeCurrency(): ?string
    {
        return $this->receiveFeeCurrency;
    }

    public function setReceiveFeeCurrency(?string $receiveFeeCurrency): self
    {
        $this->receiveFeeCurrency = $receiveFeeCurrency;

        return $this;
    }

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState(?int $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getConfirmedAt(): ?\DateTimeInterface
    {
        return $this->confirmedAt;
    }

    public function setConfirmedAt(?\DateTimeInterface $confirmedAt): self
    {
        $this->confirmedAt = $confirmedAt;

        return $this;
    }

    public function getCryptoBrokerId(): ?string
    {
        return $this->cryptoBrokerId;
    }

    public function setCryptoBrokerId(?string $cryptoBrokerId): self
    {
        $this->cryptoBrokerId = $cryptoBrokerId;

        return $this;
    }

    public function getCoinbaseTxid(): ?string
    {
        return $this->coinbaseTxid;
    }

    public function setCoinbaseTxid(?string $coinbaseTxid): self
    {
        $this->coinbaseTxid = $coinbaseTxid;

        return $this;
    }

    public function getV(): ?int
    {
        return $this->__v;
    }

    public function setV(?int $__v): self
    {
        $this->__v = $__v;

        return $this;
    }

    public function setId(?string $_id): self
    {
        $this->_id = $_id;

        return $this;
    }
}
