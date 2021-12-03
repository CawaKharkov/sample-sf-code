<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FeeRepository")
 */
class Fee
{
    const TYPE_ORDER = 1;
    const TYPE_DEPOSIT = 2;
    const TYPE_WITHDRAWAL = 3;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="bigint")
     */
    private $amount;

    /**
     * @ORM\Column(type="decimal", precision=6, scale=3)
     */
    private $makerFee;

    /**
     * @ORM\Column(type="decimal", precision=6, scale=3)
     */
    private $takerFee;

    /**
     * @ORM\Column(type="smallint")
     */
    private $type;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getMakerFee(): ?string
    {
        return $this->makerFee;
    }

    public function setMakerFee(string $makerFee): self
    {
        $this->makerFee = $makerFee;

        return $this;
    }

    public function getTakerFee(): ?string
    {
        return $this->takerFee;
    }

    public function setTakerFee(string $takerFee): self
    {
        $this->takerFee = $takerFee;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

}
