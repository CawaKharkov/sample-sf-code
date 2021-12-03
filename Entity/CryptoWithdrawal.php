<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;


/**
 * @ORM\Entity(repositoryClass="App\Repository\CryptoWithdrawalRepository")
 *
 * @JMS\ExclusionPolicy("all")
 */
class CryptoWithdrawal extends Withdrawal
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\UserWithdrawCryptoaddress", inversedBy="cryptoWithdrawals")
     * @ORM\JoinColumn(nullable=false)
     *
     * @JMS\Expose
     * @JMS\Accessor(getter="getCryptoaddressId")
     * @JMS\Type("int")
     */
    private $cryptoaddress;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $txDbId;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $txId;

    /**
     * @ORM\Column(type="decimal", precision=19, scale=8, nullable=true)
     */
    private $fee;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $chainConfirmedAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AMLInspection", mappedBy="cryptoWithdrawal")
     */
    private $amlInspections;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $amlVerifiedAt;

    public function __construct()
    {
        $this->amlInspections = new ArrayCollection();
    }

    public function getCryptoaddress(): ?UserWithdrawCryptoaddress
    {
        return $this->cryptoaddress;
    }

    public function getCryptoaddressId()
    {
        return $this->getCryptoaddressId()->getId();
    }

    public function setCryptoaddress(?UserWithdrawCryptoaddress $cryptoaddress): self
    {
        $this->cryptoaddress = $cryptoaddress;

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

    public function getFee(): ?string
    {
        return $this->fee;
    }

    public function setFee(?string $fee): self
    {
        $this->fee = $fee;

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
            $amlInspection->setCryptoWithdrawal($this);
        }

        return $this;
    }

    public function removeAmlInspection(AMLInspection $amlInspection): self
    {
        if ($this->amlInspections->contains($amlInspection)) {
            $this->amlInspections->removeElement($amlInspection);
            // set the owning side to null (unless already changed)
            if ($amlInspection->getCryptoWithdrawal() === $this) {
                $amlInspection->setCryptoWithdrawal(null);
            }
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAmlVerifiedAt()
    {
        return $this->amlVerifiedAt;
    }

    /**
     * @param mixed $amlVerifiedAt
     */
    public function setAmlVerifiedAt($amlVerifiedAt): void
    {
        $this->amlVerifiedAt = $amlVerifiedAt;
    }

}
