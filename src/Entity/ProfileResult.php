<?php

namespace App\Entity;

use App\Repository\ProfileResultRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProfileResultRepository::class)
 */
class ProfileResult
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="time")
     */
    private $time;

    /**
     * @ORM\ManyToOne(targetEntity=Race::class, inversedBy="profileResults")
     */
    private $race;

    /**
     * @ORM\OneToMany(targetEntity=ProfileCheckpoint::class, mappedBy="profileResult")
     */
    private $checkpoints;

    /**
     * @ORM\ManyToOne(targetEntity=Profile::class, inversedBy="results")
     */
    private $profile;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $place;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $disqualification;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $numberPlate;

    public function __construct()
    {
        $this->checkpoints = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTime(): ?\DateTimeInterface
    {
        return $this->time;
    }

    public function setTime(\DateTimeInterface $time): self
    {
        $this->time = $time;

        return $this;
    }

    public function getRace(): ?Race
    {
        return $this->race;
    }

    public function setRace(?Race $race): self
    {
        $this->race = $race;

        return $this;
    }

    /**
     * @return Collection|ProfileCheckpoint[]
     */
    public function getCheckpoints(): Collection
    {
        return $this->checkpoints;
    }

    public function addCheckpoint(ProfileCheckpoint $checkpoint): self
    {
        if (!$this->checkpoints->contains($checkpoint)) {
            $this->checkpoints[] = $checkpoint;
            $checkpoint->setProfileResult($this);
        }

        return $this;
    }

    public function removeCheckpoint(ProfileCheckpoint $checkpoint): self
    {
        if ($this->checkpoints->contains($checkpoint)) {
            $this->checkpoints->removeElement($checkpoint);
            // set the owning side to null (unless already changed)
            if ($checkpoint->getProfileResult() === $this) {
                $checkpoint->setProfileResult(null);
            }
        }

        return $this;
    }

    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    public function setProfile(?Profile $profile): self
    {
        $this->profile = $profile;

        return $this;
    }

    public function getPlace(): ?int
    {
        return $this->place;
    }

    public function setPlace(?int $place): self
    {
        $this->place = $place;

        return $this;
    }

    public function getDisqualification(): ?string
    {
        return $this->disqualification;
    }

    public function setDisqualification(?string $disqualification): self
    {
        $this->disqualification = $disqualification;

        return $this;
    }

    public function getNumberPlate(): ?string
    {
        return $this->numberPlate;
    }

    public function setNumberPlate(?string $numberPlate): self
    {
        $this->numberPlate = $numberPlate;

        return $this;
    }
}
