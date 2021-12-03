<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * @ORM\Entity(repositoryClass="App\Repository\DepositTransactionRepository")
 * @ORM\Table(indexes={@ORM\Index(name="referencenumber_idx", columns={"reference_number"})})
 *
 * @UniqueEntity("external_id")
 */
class DepositTransaction
{
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
     * @ORM\Column(type="string", length=255)
     */
    private $referenceNumber;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Currency")
     * @ORM\JoinColumn(nullable=false)
     */
    private $currency;

    /**
     * @ORM\Column(type="decimal", precision=19, scale=8)
     */
    private $amount;

    /**
     * @ORM\Column(type="decimal", precision=19, scale=8, nullable=true)
     */
    private $fee;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\UserDepositMethod", inversedBy="depositTransactions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $userDepositMethod;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $externalId;


    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $confirmedAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AMLInspection", mappedBy="depositTransaction")
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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getReferenceNumber(): ?string
    {
        return $this->referenceNumber;
    }

    public function setReferenceNumber(string $referenceNumber): self
    {
        $this->referenceNumber = $referenceNumber;

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

    public function setFee(string $fee): self
    {
        $this->fee = $fee;

        return $this;
    }

    public function getUserDepositMethod(): ?UserDepositMethod
    {
        return $this->userDepositMethod;
    }

    public function setUserDepositMethod(?UserDepositMethod $userDepositMethod): self
    {
        $this->userDepositMethod = $userDepositMethod;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getExternalId()
    {
        return $this->externalId;
    }

    public function setExternalId($externalId)
    {
        $this->externalId = $externalId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getConfirmedAt()
    {
        return $this->confirmedAt;
    }

    /**
     * @param mixed $confirmedAt
     */
    public function setConfirmedAt($confirmedAt)
    {
        $this->confirmedAt = $confirmedAt;
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
            $amlInspection->setDepositTransaction($this);
        }

        return $this;
    }

    public function removeAmlInspection(AMLInspection $amlInspection): self
    {
        if ($this->amlInspections->contains($amlInspection)) {
            $this->amlInspections->removeElement($amlInspection);
            // set the owning side to null (unless already changed)
            if ($amlInspection->getDepositTransaction() === $this) {
                $amlInspection->setDepositTransaction(null);
            }
        }

        return $this;
    }

}
