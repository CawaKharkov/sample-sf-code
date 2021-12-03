<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TransferRepository")
 *
 * @JMS\ExclusionPolicy("all")
 */
class Transfer
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
     * @ORM\ManyToOne(targetEntity="App\Entity\UserAccount")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotBlank
     *
     * @JMS\Expose
     * @JMS\Type("integer")
     */
    private $accountFrom;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\UserAccount")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotBlank
     *
     * @JMS\Expose
     * @JMS\Type("integer")
     */
    private $accountTo;

    /**
     * @ORM\Column(type="decimal", precision=19, scale=8)
     *
     * @Assert\NotBlank
     *
     * @JMS\Expose
     */
    private $amount;

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

    public function getAccountFrom(): ?UserAccount
    {
        return $this->accountFrom;
    }

    public function setAccountFrom(?UserAccount $accountFrom): self
    {
        $this->accountFrom = $accountFrom;

        return $this;
    }

    public function getAccountTo(): ?UserAccount
    {
        return $this->accountTo;
    }

    public function setAccountTo(?UserAccount $accountTo): self
    {
        $this->accountTo = $accountTo;

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
}
