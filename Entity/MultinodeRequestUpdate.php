<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MultinodeRequestUpdateRepository")
 */
class MultinodeRequestUpdate
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MultinodeRequestUpdateItem", mappedBy="multinodeRequestUpdate")
     */
    private $report;

    public function __construct()
    {
        $this->report = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|MultinodeRequestUpdateItem[]
     */
    public function getReport(): Collection
    {
        return $this->report;
    }

    public function addReport(MultinodeRequestUpdateItem $report): self
    {
        if (!$this->report->contains($report)) {
            $this->report[] = $report;
            $report->setMultinodeRequestUpdate($this);
        }

        return $this;
    }

    public function removeReport(MultinodeRequestUpdateItem $report): self
    {
        if ($this->report->contains($report)) {
            $this->report->removeElement($report);
            // set the owning side to null (unless already changed)
            if ($report->getMultinodeRequestUpdate() === $this) {
                $report->setMultinodeRequestUpdate(null);
            }
        }

        return $this;
    }
}
