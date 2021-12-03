<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RateRepository")
 * @ORM\Table()
 *
 */
class ExchangeRate
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
     * @ORM\Column(type="decimal", precision=19, scale=8)
     */
    private $priceAsk;

    /**
     * @ORM\Column(type="decimal", precision=19, scale=8)
     */
    private $priceBid;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Currency")
     * @ORM\JoinColumn(nullable=true)
     */
    private $currencyFrom;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Currency")
     * @ORM\JoinColumn(nullable=true)
     */
    private $currencyTo;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $code;

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

    public function getCurrencyFrom(): ?Currency
    {
        return $this->currencyFrom;
    }

    public function setCurrencyFrom(?Currency $currencyFrom): self
    {
        $this->currencyFrom = $currencyFrom;

        return $this;
    }

    public function getCurrencyTo(): ?Currency
    {
        return $this->currencyTo;
    }

    public function setCurrencyTo(?Currency $currencyTo): self
    {
        $this->currencyTo = $currencyTo;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPriceAsk()
    {
        return $this->priceAsk;
    }

    /**
     * @param $priceAsk
     * @return ExchangeRate
     */
    public function setPriceAsk($priceAsk): self
    {
        $this->priceAsk = $priceAsk;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPriceBid()
    {
        return $this->priceBid;
    }

    /**
     * @param $priceBid
     * @return ExchangeRate
     */
    public function setPriceBid($priceBid): self
    {
        $this->priceBid = $priceBid;

        return $this;
    }

}
