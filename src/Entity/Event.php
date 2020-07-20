<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EventRepository::class)
 */
class Event
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
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $link;

    /**
     * @ORM\Column(type="date")
     */
    private $date;

    /**
     * @ORM\OneToMany(targetEntity=Race::class, mappedBy="event", orphanRemoval=true)
     */
    private $race;

    public function __construct()
    {
        $this->race = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(string $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return Collection|Race[]
     */
    public function getRace(): Collection
    {
        return $this->race;
    }

    public function addRace(Race $race): self
    {
        if (!$this->race->contains($race)) {
            $this->race[] = $race;
            $race->setEvent($this);
        }

        return $this;
    }

    public function removeRace(Race $race): self
    {
        if ($this->race->contains($race)) {
            $this->race->removeElement($race);
            // set the owning side to null (unless already changed)
            if ($race->getEvent() === $this) {
                $race->setEvent(null);
            }
        }

        return $this;
    }
}
