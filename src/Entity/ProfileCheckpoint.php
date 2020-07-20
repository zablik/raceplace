<?php

namespace App\Entity;

use App\Repository\ProfileCheckpointRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProfileCheckpointRepository::class)
 */
class ProfileCheckpoint
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=Checkpoint::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $checkpoint;

    /**
     * @ORM\Column(type="time")
     */
    private $time;

    /**
     * @ORM\Column(type="time")
     */
    private $totalTime;

    /**
     * @ORM\Column(type="float")
     */
    private $speed;

    /**
     * @ORM\Column(type="float")
     */
    private $pace;

    /**
     * @ORM\ManyToOne(targetEntity=ProfileResult::class, inversedBy="checkpoints")
     */
    private $profileResult;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCheckpoint(): ?Checkpoint
    {
        return $this->checkpoint;
    }

    public function setCheckpoint(Checkpoint $checkpoint): self
    {
        $this->checkpoint = $checkpoint;

        return $this;
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

    public function getTotalTime(): ?\DateTimeInterface
    {
        return $this->totalTime;
    }

    public function setTotalTime(\DateTimeInterface $totalTime): self
    {
        $this->totalTime = $totalTime;

        return $this;
    }

    public function getSpeed(): ?float
    {
        return $this->speed;
    }

    public function setSpeed(float $speed): self
    {
        $this->speed = $speed;

        return $this;
    }

    public function getPace(): ?float
    {
        return $this->pace;
    }

    public function setPace(float $pace): self
    {
        $this->pace = $pace;

        return $this;
    }

    public function getProfileResult(): ?ProfileResult
    {
        return $this->profileResult;
    }

    public function setProfileResult(?ProfileResult $profileResult): self
    {
        $this->profileResult = $profileResult;

        return $this;
    }
}
