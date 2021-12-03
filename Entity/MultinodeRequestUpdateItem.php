<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MultinodeRequestUpdateItemRepository")
 */
class MultinodeRequestUpdateItem
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $chain;

    /**
     * @ORM\Column(type="boolean")
     */
    private $hasNewBlock;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MultinodeRequestUpdateItemResults", mappedBy="multinodeRequestUpdateItem")
     */
    private $txUpdateResults;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MultinodeRequestUpdate", inversedBy="report")
     * @ORM\JoinColumn(nullable=false)
     */
    private $multinodeRequestUpdate;

    public function __construct()
    {
        $this->txUpdateResults = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChain(): ?string
    {
        return $this->chain;
    }

    public function setChain(string $chain): self
    {
        $this->chain = $chain;

        return $this;
    }

    public function getHasNewBlock(): ?bool
    {
        return $this->hasNewBlock;
    }

    public function setHasNewBlock(bool $hasNewBlock): self
    {
        $this->hasNewBlock = $hasNewBlock;

        return $this;
    }

    /**
     * @return Collection|MultinodeRequestUpdateItemResults[]
     */
    public function getTxUpdateResults(): Collection
    {
        return $this->txUpdateResults;
    }

    public function addTxUpdateResult(MultinodeRequestUpdateItemResults $txUpdateResult): self
    {
        if (!$this->txUpdateResults->contains($txUpdateResult)) {
            $this->txUpdateResults[] = $txUpdateResult;
            $txUpdateResult->setMultinodeRequestUpdateItem($this);
        }

        return $this;
    }

    public function removeTxUpdateResult(MultinodeRequestUpdateItemResults $txUpdateResult): self
    {
        if ($this->txUpdateResults->contains($txUpdateResult)) {
            $this->txUpdateResults->removeElement($txUpdateResult);
            // set the owning side to null (unless already changed)
            if ($txUpdateResult->getMultinodeRequestUpdateItem() === $this) {
                $txUpdateResult->setMultinodeRequestUpdateItem(null);
            }
        }

        return $this;
    }

    public function getMultinodeRequestUpdate(): ?MultinodeRequestUpdate
    {
        return $this->multinodeRequestUpdate;
    }

    public function setMultinodeRequestUpdate(?MultinodeRequestUpdate $multinodeRequestUpdate): self
    {
        $this->multinodeRequestUpdate = $multinodeRequestUpdate;

        return $this;
    }
}
