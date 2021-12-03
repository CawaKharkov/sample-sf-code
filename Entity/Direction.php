<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DirectionRepository")
 *
 * @JMS\ExclusionPolicy("all")
 */
class Direction
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
     * "Base" (the currency to buy in the buy-order)
     *
     * @var Currency
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Currency", inversedBy="directionsFrom")
     * @ORM\JoinColumn(nullable=false)
     *
     * @JMS\Expose
     * @JMS\SerializedName("currency_base")
     */
    private $currencyBase;

    /**
     * "Quote" (the currency to sell in the buy-order)
     *
     * @var Currency
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Currency", inversedBy="directionsTo")
     * @ORM\JoinColumn(nullable=false)
     *
     * @JMS\Expose
     * @JMS\SerializedName("currency_quote")
     */
    private $currencyQuote;

    /**
     * @ORM\Column(type="bigint")
     *
     * @JMS\Expose
     */
    private $minimumAmount;

    /**
     * @ORM\Column(type="bigint")
     *
     * @JMS\Expose
     */
    private $maximumAmount;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Order", mappedBy="direction")
     */
    private $orders;

    /**
     * @ORM\Column(type="string", length=10)
     *
     * @JMS\Expose
     */
    private $code;

    /**
     * @ORM\Column(name="`precision`", type="smallint")
     *
     * @JMS\Expose
     */
    private $precision;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserWatchlist", mappedBy="direction")
     *
     */
    private $watchlists;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isExchange;


    public function __construct()
    {
        $this->orders = new ArrayCollection();
        $this->watchlists = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCurrencyBase(): ?Currency
    {
        return $this->currencyBase;
    }

    public function setCurrencyBase(?Currency $currencyBase): self
    {
        $this->currencyBase = $currencyBase;

        return $this;
    }

    public function getCurrencyQuote(): ?Currency
    {
        return $this->currencyQuote;
    }

    public function setCurrencyQuote(?Currency $currencyQuote): self
    {
        $this->currencyQuote = $currencyQuote;

        return $this;
    }

    public function getMinimumAmount(): ?string
    {
        return $this->minimumAmount;
    }

    public function setMinimumAmount(string $minimumAmount): self
    {
        $this->minimumAmount = $minimumAmount;

        return $this;
    }

    public function getMaximumAmount(): ?string
    {
        return $this->maximumAmount;
    }

    public function setMaximumAmount(string $maximumAmount): self
    {
        $this->maximumAmount = $maximumAmount;

        return $this;
    }

    /**
     * @return Collection|Order[]
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): self
    {
        if (!$this->orders->contains($order)) {
            $this->orders[] = $order;
            $order->setDirection($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): self
    {
        if ($this->orders->contains($order)) {
            $this->orders->removeElement($order);
            // set the owning side to null (unless already changed)
            if ($order->getDirection() === $this) {
                $order->setDirection(null);
            }
        }

        return $this;
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
     * @return Collection|UserWatchlist[]
     */
    public function getWatchlists(): Collection
    {
        return $this->watchlists;
    }

    public function addWatchlist(UserWatchlist $watchlist): self
    {
        if (!$this->watchlists->contains($watchlist)) {
            $this->watchlists[] = $watchlist;
            $watchlist->setDirection($this);
        }

        return $this;
    }

    public function removeWatchlist(UserWatchlist $watchlist): self
    {
        if ($this->watchlists->contains($watchlist)) {
            $this->watchlists->removeElement($watchlist);
            // set the owning side to null (unless already changed)
            if ($watchlist->getDirection() === $this) {
                $watchlist->setDirection(null);
            }
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsExchange()
    {
        return $this->isExchange;
    }

    /**
     * @param mixed $isExchange
     */
    public function setIsExchange($isExchange)
    {
        $this->isExchange = $isExchange;
    }


}
