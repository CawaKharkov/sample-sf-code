<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;


/**
 * @ORM\Entity(repositoryClass="App\Repository\MultinodeRequestCreateRepository")
 *
 * @JMS\ExclusionPolicy("all")
 */
class MultinodeRequestCreate
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @JMS\Expose
     * @JMS\Type("array<App\Entity\MultinodeRequestCreateItem>")
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
     * @return Collection|MultinodeRequestCreateItem[]
     */
    public function getReport(): Collection
    {
        return $this->report;
    }

    public function addReport(MultinodeRequestCreateItem $report): self
    {
        if (!$this->report->contains($report)) {
            $this->report[] = $report;
            $report->setMultinodeRequestCreate($this);
        }

        return $this;
    }

    public function removeReport(MultinodeRequestCreateItem $report): self
    {
        if ($this->report->contains($report)) {
            $this->report->removeElement($report);
            // set the owning side to null (unless already changed)
            if ($report->getMultinodeRequestCreate() === $this) {
                $report->setMultinodeRequestCreate(null);
            }
        }

        return $this;
    }
}
