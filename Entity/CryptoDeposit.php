<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * @ORM\Entity(repositoryClass="App\Repository\CryptoDepositRepository")
 */
class CryptoDeposit
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Currency")
     * @ORM\JoinColumn(nullable=false)
     */
    private $currency;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\UserDepositCryptoaddress", inversedBy="cryptoDeposits")
     * @ORM\JoinColumn(nullable=false)
     */
    private $cryptoaddress;

    /**
     * @ORM\Column(type="decimal", precision=19, scale=8)
     */
    private $amount;

    /**
     * @ORM\Column(type="decimal", precision=19, scale=8, nullable=true)
     */
    private $fee;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $txDbId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $txId;

    /**
     * @ORM\Column(type="integer")
     */
    private $confirmations;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $chainConfirmedAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AMLInspection", mappedBy="cryptoDeposit")
     */
    private $amlInspections;

    public function __construct()
    {
        $this->amlInspections = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCurrency(): ?Currency
    {
        return $this->currency;
    }

    public function setCurrency(?Currency $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getCryptoaddress(): ?UserDepositCryptoaddress
    {
        return $this->cryptoaddress;
    }

    public function setCryptoaddress(?UserDepositCryptoaddress $cryptoaddress): self
    {
        $this->cryptoaddress = $cryptoaddress;

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

    public function getFee(): ?string
    {
        return $this->fee;
    }

    public function setFee(?string $fee): self
    {
        $this->fee = $fee;

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

    public function getTxId(): ?string
    {
        return $this->txId;
    }

    public function setTxId(string $txId): self
    {
        $this->txId = $txId;

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

    public function getConfirmations(): ?int
    {
        return $this->confirmations;
    }

    public function setConfirmations(int $confirmations): self
    {
        $this->confirmations = $confirmations;

        return $this;
    }

    public function getChainConfirmedAt(): ?\DateTimeInterface
    {
        return $this->chainConfirmedAt;
    }

    public function setChainConfirmedAt(?\DateTimeInterface $chainConfirmedAt): self
    {
        $this->chainConfirmedAt = $chainConfirmedAt;

        return $this;
    }

    /**
     * @return Collection|AMLInspection[]
     */
    public function getAmlInspections(): Collection
    {
        return $this->amlInspections;
    }

    public function addAmlInspection(AMLInspection $amlInspection): self
    {
        if (!$this->amlInspections->contains($amlInspection)) {
            $this->amlInspections[] = $amlInspection;
            $amlInspection->setCryptoDeposit($this);
        }

        return $this;
    }

    public function removeAmlInspection(AMLInspection $amlInspection): self
    {
        if ($this->amlInspections->contains($amlInspection)) {
            $this->amlInspections->removeElement($amlInspection);
            // set the owning side to null (unless already changed)
            if ($amlInspection->getCryptoDeposit() === $this) {
                $amlInspection->setCryptoDeposit(null);
            }
        }

        return $this;
    }
}
