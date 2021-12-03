<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserDepositCryptoaddressRepository")
 *
 * @JMS\ExclusionPolicy("all")
 */
class UserDepositCryptoaddress
{
    const STATUS_NEW  = 1;
    const STATUS_USED = 2;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     * @JMS\Expose
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="userDepositCryptoaddresses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Currency", inversedBy="userDepositCryptoaddresses")
     * @ORM\JoinColumn(nullable=false)
     *
     * @JMS\Expose
     */
    private $currency;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @JMS\Expose
     */
    private $address;

    /**
     * @ORM\Column(type="smallint")
     *
     * @JMS\Expose
     */
    private $status = self::STATUS_NEW;

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

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        if (!array_key_exists($status, static::$statusText)) {
            throw new BadRequestHttpException(sprintf("Invalid address status: %s. Only supported: %s",
                    $status,
                    implode(',', array_keys(static::$statusText)))
            );
        }

        $this->status = $status;

        return $this;
    }

    public function getStatusText(): string
    {
        return static::$statusText[$this->getStatus()];
    }

    public static $statusText = [
        self::STATUS_NEW  => 'New',
        self::STATUS_USED => 'User',
    ];

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CryptoDeposit", mappedBy="cryptoaddress")
     */
    private $cryptoDeposits;

    public function __construct()
    {
        $this->cryptoDeposits = new ArrayCollection();
    }

    /**
     * @return Collection|CryptoDeposit[]
     */
    public function getCryptoDeposits(): Collection
    {
        return $this->cryptoDeposits;
    }

    public function addCryptoDeposit(CryptoDeposit $cryptoDeposit): self
    {
        if (!$this->cryptoDeposits->contains($cryptoDeposit)) {
            $this->cryptoDeposits[] = $cryptoDeposit;
            $cryptoDeposit->setCryptoaddress($this);
        }

        return $this;
    }

    public function removeCryptoDeposit(CryptoDeposit $cryptoDeposit): self
    {
        if ($this->cryptoDeposits->contains($cryptoDeposit)) {
            $this->cryptoDeposits->removeElement($cryptoDeposit);
            // set the owning side to null (unless already changed)
            if ($cryptoDeposit->getCryptoaddress() === $this) {
                $cryptoDeposit->setCryptoaddress(null);
            }
        }

        return $this;
    }

}
