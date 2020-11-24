<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/** @ORM\Embeddable() */
class RaceResultsSource
{
    const SOURCE_OBELARUS = 'obelarus';
    const SOURCE_ARF = 'arf';

    const TYPE_GENERAL = 'general';
    const TYPE_NO_GROUP = 'no_group';
    const TYPE_WITH_PENALTY = 'with_penalty';

    public function __construct()
    {
        $this->codes = [];
    }

    public static function getTypes()
    {
        return [
            self::SOURCE_OBELARUS,
            self::SOURCE_ARF,
        ];
    }

    public static function getConfigTypes()
    {
        return [
            self::TYPE_GENERAL,
            self::TYPE_NO_GROUP,
            self::TYPE_WITH_PENALTY,
        ];
    }

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Choice({
     *     RaceResultsSource::SOURCE_OBELARUS,
     *     RaceResultsSource::SOURCE_ARF,
     * })
     */
    private string $type;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $link;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Choice({
     *     RaceResultsSource::TYPE_GENERAL,
     *     RaceResultsSource::TYPE_NO_GROUP,
     *     RaceResultsSource::TYPE_WITH_PENALTY,
     * })
     */
    private ?string $tableConfigType;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
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
    public function getTableConfigType(): ?string
    {
        return $this->tableConfigType;
    }

    /**
     * @param string $tableConfigType
     */
    public function setTableConfigType(?string $tableConfigType): self
    {
        $this->tableConfigType = $tableConfigType;

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
