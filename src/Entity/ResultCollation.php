<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

class ResultCollation
{
    /**
     * @ORM\Column(type="integer")
     */
    private $profileId;

    /**
     * @ORM\Column(type="float")
     */
    private $ratio;

    private ?Profile $profile;

    public function getProfileId(): int
    {
        return $this->profileId;
    }

    public function setProfileId(int $profileId): self
    {
        $this->profileId = $profileId;

        return $this;
    }

    public function getRatio(): float
    {
        return $this->ratio;
    }

    public function setRatio(float $ratio): self
    {
        $this->ratio = $ratio;

        return $this;
    }

    /**
     * @return Profile|null
     */
    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    /**
     * @param Profile $profile
     * @return ResultCollation
     */
    public function setProfile(Profile $profile): ResultCollation
    {
        $this->profile = $profile;
        return $this;
    }
}
