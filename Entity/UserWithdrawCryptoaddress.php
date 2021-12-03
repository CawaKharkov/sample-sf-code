<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserWithdrawCryptoaddressRepository")
 *
 * @JMS\ExclusionPolicy("all")
 *
 */
class UserWithdrawCryptoaddress
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
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="userWithdrawCryptoaddresses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Currency", inversedBy="userWithdrawCryptoaddresses")
     * @ORM\JoinColumn(nullable=false)
     *
     * @JMS\Expose
     */
    private $currency;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank
     *
     * @JMS\Expose
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @Assert\NotBlank
     *
     * @JMS\Expose
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="CryptoWithdrawal", mappedBy="cryptoaddress")
     */
    private $cryptoWithdrawals;

    public function __construct()
    {
        $this->cryptoWithdrawals = new ArrayCollection();
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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

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

    /**
     * @return Collection|CryptoWithdrawal[]
     */
    public function getCryptoWithdrawals(): Collection
    {
        return $this->cryptoWithdrawals;
    }

    public function addCryptoWithdrawal(CryptoWithdrawal $withdrawal): self
    {
        if (!$this->cryptoWithdrawals->contains($withdrawal)) {
            $this->cryptoWithdrawals[] = $withdrawal;
            $withdrawal->setCryptoaddress($this);
        }

        return $this;
    }

    public function removeCryptoWithdrawal(CryptoWithdrawal $withdrawal): self
    {
        if ($this->cryptoWithdrawals->contains($withdrawal)) {
            $this->cryptoWithdrawals->removeElement($withdrawal);
            // set the owning side to null (unless already changed)
            if ($withdrawal->getCryptoaddress() === $this) {
                $withdrawal->setCryptoaddress(null);
            }
        }

        return $this;
    }
}
