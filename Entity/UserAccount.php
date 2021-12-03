<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserAccountRepository")
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(columns={"user_id", "currency_id", "type"})})
 * @UniqueEntity(
 *     fields={"user", "currency", "type"},
 *     errorPath="user",
 *     message="User already has this account"
 * )
 *
 * @JMS\ExclusionPolicy("all")
 */
class UserAccount
{
    const TYPE_PRIMARY = 1;
    const TYPE_HOLD    = 2;

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
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="userAccounts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Currency", inversedBy="userAccounts")
     * @ORM\JoinColumn(nullable=false)
     *
     * @JMS\Expose
     * @JMS\Groups({"list", "profile"})
     */
    private $currency;

    /**
     * @ORM\Column(type="decimal", precision=19, scale=8)
     *
     * @JMS\Expose
     * @JMS\Type("string")
     * @JMS\Groups({"profile"})
     */
    private $balance;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Transaction", mappedBy="userAccount")
     */
    private $transactions;

    /**
     * @ORM\Column(type="smallint")
     */
    private $type;

    /**
     * @JMS\Expose
     * @JMS\Type("string")
     * @JMS\Groups({"profile"})
     */
    private $rate;

    /**
     * @JMS\Expose
     * @JMS\Type("string")
     * @JMS\Groups({"profile"})
     */
    private $change;

    /**
     * @JMS\Expose
     * @JMS\Type("string")
     * @JMS\Groups({"profile"})
     */
    private $value;


    public static $typesText = [
        self::TYPE_PRIMARY => 'Primary',
        self::TYPE_HOLD    => 'Hold',
    ];

    /**
     * @ORM\OneToMany(targetEntity=ExchangeBonus::class, mappedBy="account")
     */
    private $exchangeBonuses;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
        $this->exchangeBonuses = new ArrayCollection();
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

    public function getBalance(): ?string
    {
        return Currency::TYPE_FIAT == $this->getCurrency()->getType()
            ? round($this->balance, 2)
            : $this->balance;
    }

    public function setBalance(string $balance): self
    {
        $this->balance = $balance;

        return $this;
    }

    /**
     * @return Collection|Transaction[]
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): self
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions[] = $transaction;
            $transaction->setUserAccount($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): self
    {
        if ($this->transactions->contains($transaction)) {
            $this->transactions->removeElement($transaction);
            // set the owning side to null (unless already changed)
            if ($transaction->getUserAccount() === $this) {
                $transaction->setUserAccount(null);
            }
        }

        return $this;
    }

    public function getRate()
    {
        $this->rate;
    }

    public function setRate($rate): self
    {
        $this->rate = $rate;

        return $this;
    }

    public function getChange()
    {
        $this->change;
    }

    public function setChange($change): self
    {
        $this->change = $change;

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        if (!array_key_exists($type, static::$typesText)) {
            throw new BadRequestHttpException(sprintf("Invalid account type: %s. Only supported: %s",
                    $type,
                    implode(',', array_keys(static::$typesText)))
            );
        }

        $this->type = $type;

        return $this;
    }

    public function getTypeText(): string
    {
        return static::$typesText[$this->getType()];
    }

    public function getUserEmailAndCurrencyCode()
    {
        return sprintf("%s : %s %s (%s%s)",
            $this->getUser()->getEmail(),
            $this->getCurrency()->getCode(),
            $this->getTypeText(),
            $this->getBalance(),
            $this->getCurrency()->getCode()
        );
    }

    /**
     * @return Collection|ExchangeBonus[]
     */
    public function getExchangeBonuses(): Collection
    {
        return $this->exchangeBonuses;
    }

    public function addExchangeBonus(ExchangeBonus $exchangeBonus): self
    {
        if (!$this->exchangeBonuses->contains($exchangeBonus)) {
            $this->exchangeBonuses[] = $exchangeBonus;
            $exchangeBonus->setAccount($this);
        }

        return $this;
    }

    public function removeExchangeBonus(ExchangeBonus $exchangeBonus): self
    {
        if ($this->exchangeBonuses->removeElement($exchangeBonus)) {
            // set the owning side to null (unless already changed)
            if ($exchangeBonus->getAccount() === $this) {
                $exchangeBonus->setAccount(null);
            }
        }

        return $this;
    }


}
