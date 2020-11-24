<?php

namespace App\Entity;

use App\Repository\RaceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=RaceRepository::class)
 * @UniqueEntity(fields={"slug", "event"})
 * @ORM\Table(
 *     indexes={
 *          @ORM\Index(name="type", columns={"type"}),
 *          @ORM\Index(name="race_slug", columns={"slug"}),
 *          @ORM\Index(name="distance", columns={"distance"})
 *     },
 *     uniqueConstraints={@ORM\UniqueConstraint(name="race_event_slug", columns={"slug", "event_id"})}
 * )
 */
class Race
{
    use TimestampableEntity;

    const TYPE__TRAIL = 'trail';
    const TYPE__NIGHT_TRAIL = 'night-trail';
    const TYPE__MARATHON = 'marathon';
    const TYPE__NIGHT_MARATHON = 'night-marathon';
    const TYPE__XCM = 'xcm';
    const TYPE__XCO = 'xco';
    const TYPE__NIGHT_XCM = 'night-xcm';
    const TYPE__MULTI = 'multi';

    public static function getTypes()
    {
        return [
            self::TYPE__TRAIL,
            self::TYPE__NIGHT_TRAIL,
            self::TYPE__MARATHON,
            self::TYPE__NIGHT_MARATHON,
            self::TYPE__XCM,
            self::TYPE__XCO,
            self::TYPE__NIGHT_XCM,
            self::TYPE__MULTI,
        ];
    }

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Choice({
     *     Race::TYPE__TRAIL,
     *     Race::TYPE__NIGHT_TRAIL,
     *     Race::TYPE__MARATHON,
     *     Race::TYPE__NIGHT_MARATHON,
     *     Race::TYPE__XCM,
     *     Race::TYPE__XCO,
     *     Race::TYPE__NIGHT_XCM,
     *     Race::TYPE__MULTI,
     * })
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $distance;

    /**
     * @ORM\ManyToOne(targetEntity=Event::class, inversedBy="race")
     * @ORM\JoinColumn(nullable=false)
     */
    private $event;

    /**
     * @ORM\OneToMany(targetEntity=Checkpoint::class, mappedBy="race", cascade={"all"}, orphanRemoval=true)
     * @ORM\OrderBy({"distance" = "ASC"})
     */
    private $checkpoints;

    /**
     * @ORM\OneToMany(targetEntity=ProfileResult::class, mappedBy="race", cascade={"all"}, orphanRemoval=true)
     */
    private $profileResults;

    /**
     * @ORM\Embedded(class="RaceResultsSource", columnPrefix="results_source_")
     */
    private $resultsSource;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    public function __construct()
    {
        $this->checkpoints = new ArrayCollection();
        $this->profileResults = new ArrayCollection();
        $this->resultsSource = new RaceResultsSource();
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

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDistance(): ?float
    {
        return $this->distance;
    }

    public function setDistance(?float $distance): self
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

    /**
     * @return RaceResultsSource
     */
    public function getResultsSource(): ?RaceResultsSource
    {
        return $this->resultsSource;
    }

    /**
     * @param RaceResultsSource $resultsSource
     * @return Race
     */
    public function setResultsSource($resultsSource): self
    {
        $this->resultsSource = $resultsSource;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function __toString()
    {
        return implode(';', [
            'Type: ' . $this->getType(),
            'Distance: ' . $this->getDistance(),
            'Slug: ' . $this->getSlug(),
            'Name: ' . $this->getName(),
            'Event: ' . !empty($this->getEvent()) ? $this->getEvent()->getName() : '',
        ]);
    }

    public function __clone()
    {
        $this->id = null;
        $this->resultsSource = clone $this->resultsSource;
    }
}
