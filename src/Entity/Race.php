<?php

namespace App\Entity;

use App\Repository\RaceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RaceRepository::class)
 */
class Race
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $distance;

    /**
     * @ORM\ManyToOne(targetEntity=Event::class, inversedBy="race")
     * @ORM\JoinColumn(nullable=false)
     */
    private $event;

    /**
     * @ORM\OneToMany(targetEntity=Checkpoint::class, mappedBy="race", orphanRemoval=true)
     */
    private $checkpoints;

    /**
     * @ORM\OneToMany(targetEntity=ProfileResult::class, mappedBy="race")
     */
    private $profileResults;

    public function __construct()
    {
        $this->checkpoints = new ArrayCollection();
        $this->profileResults = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDistance(): ?int
    {
        return $this->distance;
    }

    public function setDistance(?int $distance): self
    {
        $this->distance = $distance;

        return $this;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): self
    {
        $this->event = $event;

        return $this;
    }

    /**
     * @return Collection|Checkpoint[]
     */
    public function getCheckpoints(): Collection
    {
        return $this->checkpoints;
    }

    public function addCheckpoint(Checkpoint $checkpoint): self
    {
        if (!$this->checkpoints->contains($checkpoint)) {
            $this->checkpoints[] = $checkpoint;
            $checkpoint->setRace($this);
        }

        return $this;
    }

    public function removeCheckpoint(Checkpoint $checkpoint): self
    {
        if ($this->checkpoints->contains($checkpoint)) {
            $this->checkpoints->removeElement($checkpoint);
            // set the owning side to null (unless already changed)
            if ($checkpoint->getRace() === $this) {
                $checkpoint->setRace(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ProfileResult[]
     */
    public function getProfileResults(): Collection
    {
        return $this->profileResults;
    }

    public function addProfileResult(ProfileResult $profileResult): self
    {
        if (!$this->profileResults->contains($profileResult)) {
            $this->profileResults[] = $profileResult;
            $profileResult->setRace($this);
        }

        return $this;
    }

    public function removeProfileResult(ProfileResult $profileResult): self
    {
        if ($this->profileResults->contains($profileResult)) {
            $this->profileResults->removeElement($profileResult);
            // set the owning side to null (unless already changed)
            if ($profileResult->getRace() === $this) {
                $profileResult->setRace(null);
            }
        }

        return $this;
    }
}
