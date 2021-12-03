<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FiatWithdrawalRepository")
 *
 * @JMS\ExclusionPolicy("all")
 */
class FiatWithdrawal extends Withdrawal
{

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\UserWithdrawAccount", inversedBy="fiatWithdrawals")
     * @ORM\JoinColumn(nullable=false)
     *
     * @JMS\Expose
     * @JMS\Accessor(getter="getAccountId")
     * @JMS\Type("int")
     */
    private $account;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AMLInspection", mappedBy="fiatWithdrawal")
     */
    private $amlInspections;

    public function __construct()
    {
        $this->amlInspections = new ArrayCollection();
    }

    public function getAccount(): ?UserWithdrawAccount
    {
        return $this->account;
    }

    public function setAccount(?UserWithdrawAccount $account): self
    {
        $this->account = $account;

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
            $amlInspection->setFiatWithdrawal($this);
        }

        return $this;
    }

    public function removeAmlInspection(AMLInspection $amlInspection): self
    {
        if ($this->amlInspections->contains($amlInspection)) {
            $this->amlInspections->removeElement($amlInspection);
            // set the owning side to null (unless already changed)
            if ($amlInspection->getFiatWithdrawal() === $this) {
                $amlInspection->setFiatWithdrawal(null);
            }
        }

        return $this;
    }

}
