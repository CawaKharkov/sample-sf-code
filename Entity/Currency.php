<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;


/**
 * @ORM\Entity(repositoryClass="App\Repository\CurrencyRepository")
 *
 * @JMS\ExclusionPolicy("all")
 */
class Currency
{
    const TYPE_FIAT = 1;
    const TYPE_CRYPTO = 2;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     * @JMS\Expose
     * @JMS\Groups({"list", "profile"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=10)
     *
     * @JMS\Expose
     * @JMS\Groups({"list", "profile"})
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=50)
     *
     * @JMS\Expose
     * @JMS\Groups({"list", "profile"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $symbol;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserAccount", mappedBy="currency")
     */
    private $userAccounts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Direction", mappedBy="currencyBase")
     */
    private $directionsFrom;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Direction", mappedBy="currencyQuote")
     */
    private $directionsTo;

    /**
     * @ORM\Column(name="`precision`", type="smallint")
     *
     * @JMS\Expose
     */
    private $precision;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Rate", mappedBy="currency")
     */
    private $rates;

    /**
     * @ORM\OneToMany(targetEntity="UserDepositCryptoaddress", mappedBy="currency")
     */
    private $userDepositCryptoaddresses;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     *
     * @JMS\Expose
     */
    private $type;

    public function __construct()
    {
        $this->userAccounts = new ArrayCollection();
        $this->directionsFrom = new ArrayCollection();
        $this->directionsTo = new ArrayCollection();
        $this->rates = new ArrayCollection();
        $this->userDepositCryptoaddresses = new ArrayCollection();
        $this->userWithdrawCryptoaddresses = new ArrayCollection();
    }

    public static $typesText = [
        self::TYPE_FIAT    => 'Fiat',
        self::TYPE_CRYPTO  => 'Crypto',
    ];

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserWithdrawCryptoaddress", mappedBy="currency")
     */
    private $userWithdrawCryptoaddresses;

    // TODO add: private $userWithdrawAccounts (inversed);

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function setSymbol(string $symbol): self
    {
        $this->symbol = $symbol;

        return $this;
    }

    /**
     * @return Collection|UserAccount[]
     */
    public function getUserAccounts(): Collection
    {
        return $this->userAccounts;
    }

    public function addUserAccount(UserAccount $userAccount): self
    {
        if (!$this->userAccounts->contains($userAccount)) {
            $this->userAccounts[] = $userAccount;
            $userAccount->setCurrency($this);
        }

        return $this;
    }

    public function removeUserAccount(UserAccount $userAccount): self
    {
        if ($this->userAccounts->contains($userAccount)) {
            $this->userAccounts->removeElement($userAccount);
            // set the owning side to null (unless already changed)
            if ($userAccount->getCurrency() === $this) {
                $userAccount->setCurrency(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Direction[]
     */
    public function getDirectionsFrom(): Collection
    {
        return $this->directionsFrom;
    }

    public function addDirectionFrom(Direction $direction): self
    {
        if (!$this->directionsFrom->contains($direction)) {
            $this->directionsFrom[] = $direction;
            $direction->setCurrencyBase($this);
        }

        return $this;
    }

    public function removeDirectionFrom(Direction $direction): self
    {
        if ($this->directionsFrom->contains($direction)) {
            $this->directionsFrom->removeElement($direction);
            // set the owning side to null (unless already changed)
            if ($direction->getCurrencyBase() === $this) {
                $direction->setCurrencyBase(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Direction[]
     */
    public function getDirectionsTo(): Collection
    {
        return $this->directionsTo;
    }

    public function addDirectionsTo(Direction $directionsTo): self
    {
        if (!$this->directionsTo->contains($directionsTo)) {
            $this->directionsTo[] = $directionsTo;
            $directionsTo->setCurrencyQuote($this);
        }

        return $this;
    }

    public function removeDirectionsTo(Direction $directionsTo): self
    {
        if ($this->directionsTo->contains($directionsTo)) {
            $this->directionsTo->removeElement($directionsTo);
            // set the owning side to null (unless already changed)
            if ($directionsTo->getCurrencyQuote() === $this) {
                $directionsTo->setCurrencyQuote(null);
            }
        }

        return $this;
    }

    public function getPrecision(): ?int
    {
        return $this->precision;
    }

    public function setPrecision(int $precision): self
    {
        $this->precision = $precision;

        return $this;
    }

    /**
     * @return Collection|Rate[]
     */
    public function getRates(): Collection
    {
        return $this->rates;
    }

    public function addRate(Rate $rate): self
    {
        if (!$this->rates->contains($rate)) {
            $this->rates[] = $rate;
            $rate->setCurrency($this);
        }

        return $this;
    }

    public function removeRate(Rate $rate): self
    {
        if ($this->rates->contains($rate)) {
            $this->rates->removeElement($rate);
            // set the owning side to null (unless already changed)
            if ($rate->getCurrency() === $this) {
                $rate->setCurrency(null);
            }
        }

        return $this;
    }

    /**
     * @return ?Rate
     */
    public function getLatestRate()
    {
        // todo: order by date desc, not first in collection
        return $this->getRates()->first();
    }

    /**
     * @return Collection|UserDepositCryptoaddress[]
     */
    public function getUserDepositCryptoaddresses(): Collection
    {
        return $this->userDepositCryptoaddresses;
    }

    public function addCryptoAddress(UserDepositCryptoaddress $cryptoAddress): self
    {
        if (!$this->userDepositCryptoaddresses->contains($cryptoAddress)) {
            $this->userDepositCryptoaddresses[] = $cryptoAddress;
            $cryptoAddress->setCurrency($this);
        }

        return $this;
    }

    public function removeCryptoAddress(UserDepositCryptoaddress $cryptoAddress): self
    {
        if ($this->userDepositCryptoaddresses->contains($cryptoAddress)) {
            $this->userDepositCryptoaddresses->removeElement($cryptoAddress);
            // set the owning side to null (unless already changed)
            if ($cryptoAddress->getCurrency() === $this) {
                $cryptoAddress->setCurrency(null);
            }
        }

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTypeText(): string
    {
        return static::$typesText[$this->getType()];
    }

    /**
     * @return Collection|UserWithdrawCryptoaddress[]
     */
    public function getUserWithdrawCryptoaddresses(): Collection
    {
        return $this->userWithdrawCryptoaddresses;
    }

    public function addWithdrawCryptoaddress(UserWithdrawCryptoaddress $withdrawCryptoaddress): self
    {
        if (!$this->userWithdrawCryptoaddresses->contains($withdrawCryptoaddress)) {
            $this->userWithdrawCryptoaddresses[] = $withdrawCryptoaddress;
            $withdrawCryptoaddress->setCurrency($this);
        }

        return $this;
    }

    public function removeWithdrawCryptoaddress(UserWithdrawCryptoaddress $withdrawCryptoaddress): self
    {
        if ($this->userWithdrawCryptoaddresses->contains($withdrawCryptoaddress)) {
            $this->userWithdrawCryptoaddresses->removeElement($withdrawCryptoaddress);
            // set the owning side to null (unless already changed)
            if ($withdrawCryptoaddress->getCurrency() === $this) {
                $withdrawCryptoaddress->setCurrency(null);
            }
        }

        return $this;
    }

}
