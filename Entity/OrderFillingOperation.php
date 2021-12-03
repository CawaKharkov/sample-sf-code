<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OrderFillingOperationRepository")
 */
class OrderFillingOperation
{
    const SIDE_TAKER = 1;
    const SIDE_MAKER = 2;

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
     * @ORM\ManyToOne(targetEntity="App\Entity\Order", inversedBy="operations")
     * @ORM\JoinColumn(onDelete="SET NULL"))
     */
    private $order;

    /**
     * @ORM\Column(type="decimal", precision=19, scale=8)
     */
    private $amount;

    /**
     * @ORM\Column(type="decimal", precision=19, scale=8, nullable=true)
     */
    private $price;

    /**
     * @ORM\Column(type="smallint")
     */
    private $side;

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

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): self
    {
        $this->order = $order;

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

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getSide(): ?int
    {
        return $this->side;
    }

    public function setSide(int $side): self
    {
        $this->side = $side;

        return $this;
    }
}
