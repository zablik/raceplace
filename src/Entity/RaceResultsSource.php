<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\Embeddable() */
class RaceResultsSource
{
    const SOURCE_OBELARUS = 'obelarus';

    public function __construct()
    {
        $this->codes = [];
    }

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Choice({
     *     RaceResultsSource::SOURCE_OBELARUS
     * })
     */
    private string $type;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $link;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $checkpointsLink;

    /**
     * @ORM\Column(type="array")
     */
    private $codes = [];

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return RaceResultsSource
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getLink(): ?string
    {
        return $this->link;
    }

    /**
     * @param string $link
     * @return RaceResultsSource
     */
    public function setLink($link): self
    {
        $this->link = $link;

        return $this;
    }

    /**
     * @return string
     */
    public function getCheckpointsLink(): ?string
    {
        return $this->checkpointsLink;
    }

    /**
     * @param string $checkpointsLink
     * @return RaceResultsSource
     */
    public function setCheckpointsLink(string $checkpointsLink): self
    {
        $this->checkpointsLink = $checkpointsLink;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getCodes()
    {
        return $this->codes ?: [];
    }

    /**
     * @param string[] $codes
     * @return RaceResultsSource
     */
    public function setCodes($codes): self
    {
        $this->codes = $codes;

        return $this;
    }
}
