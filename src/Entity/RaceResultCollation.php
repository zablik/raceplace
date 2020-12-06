<?php

namespace App\Entity;

use App\Repository\RaceResultCollationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RaceResultCollationRepository::class)
 */
class RaceResultCollation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Profile::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $profile;

    /**
     * @var ResultCollation[]
     * @ORM\Column(name="collations", type="race_result_collations_type")
     */
    private $collations;

    /**
     * @ORM\ManyToOne(targetEntity=Race::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $race;

    public function __construct()
    {
        $this->collations = [];
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return ResultCollation[]|array
     */
    public function getCollations(): array
    {
        return $this->collations;
    }

    public function addCollation(ResultCollation $collation): self
    {
        $this->collations[] = $collation;

        return $this;
    }

    public function setCollations(array $collations): self
    {
        $this->collations = $collations;

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
}
