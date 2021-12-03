<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserDepositMethodRepository")
 *
 * @UniqueEntity("reference")
 */
class UserDepositMethod
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Currency")
     * @ORM\JoinColumn(nullable=false)
     */
    private $currency;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="userDepositMethods")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $reference;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\DepositTransaction", mappedBy="userDepositMethod")
     */
    private $depositTransactions;

    public function __construct()
    {
        $this->depositTransactions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * @return Collection|DepositTransaction[]
     */
    public function getDepositTransactions(): Collection
    {
        return $this->depositTransactions;
    }

    public function addDepositTransaction(DepositTransaction $depositTransaction): self
    {
        if (!$this->depositTransactions->contains($depositTransaction)) {
            $this->depositTransactions[] = $depositTransaction;
            $depositTransaction->setUserDepositMethod($this);
        }

        return $this;
    }

    public function removeDepositTransaction(DepositTransaction $depositTransaction): self
    {
        if ($this->depositTransactions->contains($depositTransaction)) {
            $this->depositTransactions->removeElement($depositTransaction);
            // set the owning side to null (unless already changed)
            if ($depositTransaction->getUserDepositMethod() === $this) {
                $depositTransaction->setUserDepositMethod(null);
            }
        }

        return $this;
    }
}
