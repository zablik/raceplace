<?php

namespace App\Entity;

use App\Repository\ProfileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ProfileRepository::class)
 * @UniqueEntity(
 *     fields={"name", "birthday"},
 *     errorPath="port",
 *     message="This port is already in use on that host."
 * )
 */
class Profile
{
    const GROUP__FEMALE = 'female';
    const GROUP__MALE = 'male';

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
     * @ORM\Column(type="date", nullable=true)
     */
    private $birthday;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $region;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $club;

    /**
     * @ORM\Column(name ="`group`", type="string", length=255, nullable=true)
     * @Assert\Choice({
     *     Profile::GROUP__FEMALE,
     *     Profile::GROUP__MALE,
     * })
     */
    private $group;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $stravaId;

    /**
     * @ORM\OneToOne(targetEntity=User::class, inversedBy="profile", cascade={"persist", "remove"})
     */
    private $user;

    /**
     * @var ProfileResult[]|ArrayCollection
     * @ORM\OneToMany(targetEntity=ProfileResult::class, mappedBy="profile")
     */
    private $results;

    public function __construct()
    {
        $this->results = new ArrayCollection();
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

    public function getBirthday(): ?\DateTimeInterface
    {
        return $this->birthday;
    }

    public function setBirthday(?\DateTimeInterface $birthday): self
    {
        $this->birthday = $birthday;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): self
    {
        $this->region = $region;

        return $this;
    }

    public function getClub(): ?string
    {
        return $this->club;
    }

    public function setClub(?string $club): self
    {
        $this->club = $club;

        return $this;
    }

    public function getGroup(): ?string
    {
        return $this->group;
    }

    public function setGroup(?string $group): self
    {
        $this->group = $group;

        return $this;
    }

    public function getStravaId(): ?string
    {
        return $this->stravaId;
    }

    public function setStravaId(?string $stravaId): self
    {
        $this->stravaId = $stravaId;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection|ProfileResult[]
     */
    public function getResults(): Collection
    {
        return $this->results;
    }

    public function addResult(ProfileResult $result): self
    {
        if (!$this->results->contains($result)) {
            $this->results[] = $result;
            $result->setProfile($this);
        }

        return $this;
    }

    public function removeResult(ProfileResult $result): self
    {
        if ($this->results->contains($result)) {
            $this->results->removeElement($result);
            // set the owning side to null (unless already changed)
            if ($result->getProfile() === $this) {
                $result->setProfile(null);
            }
        }

        return $this;
    }
}
