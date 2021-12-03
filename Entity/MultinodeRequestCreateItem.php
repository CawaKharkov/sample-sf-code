<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;


/**
 * @ORM\Entity(repositoryClass="App\Repository\MultinodeRequestCreateItemRepository")
 *
 * @JMS\ExclusionPolicy("all")
 */
class MultinodeRequestCreateItem
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     *
     * @JMS\Expose
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @JMS\Expose
     */
    private $txDbId;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @JMS\Expose
     */
    private $txId;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @JMS\Expose
     */
    private $userId;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MultinodeRequestCreate", inversedBy="report", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $multinodeRequestCreate;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getTxDbId(): ?string
    {
        return $this->txDbId;
    }

    public function setTxDbId(string $txDbId): self
    {
        $this->txDbId = $txDbId;

        return $this;
    }

    public function getTxId(): ?string
    {
        return $this->txId;
    }

    public function setTxId(string $txId): self
    {
        $this->txId = $txId;

        return $this;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function setUserId(string $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getMultinodeRequestCreate(): ?MultinodeRequestCreate
    {
        return $this->multinodeRequestCreate;
    }

    public function setMultinodeRequestCreate(?MultinodeRequestCreate $multinodeRequestCreate): self
    {
        $this->multinodeRequestCreate = $multinodeRequestCreate;

        return $this;
    }
}
