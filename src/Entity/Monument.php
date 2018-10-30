<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MonumentRepository")
 */
class Monument
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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $location;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $height;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $unesco_status;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $builder;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $purpose;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $condition;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $major_event;

    /**
     * @ORM\Column(type="string", length=8192, nullable=true)
     */
    private $tags;

    /**
     * @ORM\Column(type="string", length=1048576, nullable=true)
     */
    private $images;

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

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(?string $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getHeight(): ?string
    {
        return $this->height;
    }

    public function setHeight(?string $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getUnescoStatus(): ?string
    {
        return $this->unesco_status;
    }

    public function setUnescoStatus(?string $unesco_status): self
    {
        $this->unesco_status = $unesco_status;

        return $this;
    }

    public function getBuilder(): ?string
    {
        return $this->builder;
    }

    public function setBuilder(?string $builder): self
    {
        $this->builder = $builder;

        return $this;
    }

    public function getPurpose(): ?string
    {
        return $this->purpose;
    }

    public function setPurpose(?string $purpose): self
    {
        $this->purpose = $purpose;

        return $this;
    }

    public function getCondition(): ?string
    {
        return $this->condition;
    }

    public function setCondition(?string $condition): self
    {
        $this->condition = $condition;

        return $this;
    }

    public function getMajorEvent(): ?string
    {
        return $this->major_event;
    }

    public function setMajorEvent(?string $major_event): self
    {
        $this->major_event = $major_event;

        return $this;
    }

    public function getTags(): ?string
    {
        return $this->tags;
    }

    public function setTags(?string $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    public function getImages(): ?string
    {
        return $this->images;
    }

    public function setImages(?string $images): self
    {
        $this->images = $images;

        return $this;
    }
}
