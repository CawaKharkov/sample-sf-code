<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;


/**
 * @ORM\Entity(repositoryClass="App\Repository\UserWithdrawAccountRepository")
 *
 * @JMS\ExclusionPolicy("all")
 */
class UserWithdrawAccount
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     * @JMS\Expose
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="bankAccounts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Currency")
     * @ORM\JoinColumn(nullable=false)
     *
     * @JMS\Expose
     * @JMS\Accessor(getter="getCurrencyId")
     * @JMS\Type("int")
     */
    private $currency;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @JMS\Expose
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @JMS\Expose
     */
    private $nameOnAccount;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     *
     * @JMS\Expose
     */
    private $iban;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     *
     * @JMS\Expose
     */
    private $bankName;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     *
     * @JMS\Expose
     */
    private $bic;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @JMS\Expose
     */
    private $address;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\FiatWithdrawal", mappedBy="account")
     */
    private $fiatWithdrawals;

    public function __construct()
    {
        $this->fiatWithdrawals = new ArrayCollection();
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

    public function getCurrencyId(): ?int
    {
        return $this->getCurrency()->getId();
    }

    public function setCurrency(?Currency $currency): self
    {
        $this->currency = $currency;

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

    public function getNameOnAccount(): ?string
    {
        return $this->nameOnAccount;
    }

    public function setNameOnAccount(?string $nameOnAccount): self
    {
        $this->nameOnAccount = $nameOnAccount;

        return $this;
    }

    public function getIban(): ?string
    {
        return $this->iban;
    }

    public function setIban(string $iban): self
    {
        $this->iban = $iban;

        return $this;
    }

    public function getBankName(): ?string
    {
        return $this->bankName;
    }

    public function setBankName(?string $bankName): self
    {
        $this->bankName = $bankName;

        return $this;
    }

    public function getBic(): ?string
    {
        return $this->bic;
    }

    public function setBic(?string $bic): self
    {
        $this->bic = $bic;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return Collection|FiatWithdrawal[]
     */
    public function getFiatWithdrawals(): Collection
    {
        return $this->fiatWithdrawals;
    }

    public function addFiatWithdrawal(FiatWithdrawal $fiatWithdrawal): self
    {
        if (!$this->fiatWithdrawals->contains($fiatWithdrawal)) {
            $this->fiatWithdrawals[] = $fiatWithdrawal;
            $fiatWithdrawal->setAccount($this);
        }

        return $this;
    }

    public function removeFiatWithdrawal(FiatWithdrawal $fiatWithdrawal): self
    {
        if ($this->fiatWithdrawals->contains($fiatWithdrawal)) {
            $this->fiatWithdrawals->removeElement($fiatWithdrawal);
            // set the owning side to null (unless already changed)
            if ($fiatWithdrawal->getAccount() === $this) {
                $fiatWithdrawal->setAccount(null);
            }
        }

        return $this;
    }
}
