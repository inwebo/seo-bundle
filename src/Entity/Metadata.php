<?php

declare(strict_types=1);

namespace Inwebo\SeoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Inwebo\SeoBundle\Model\MetadataInterface;
use Symfony\Component\Uid\UuidV6 as Uuid;

#[ORM\MappedSuperclass]
class Metadata implements MetadataInterface
{
    public const string CONFIG_KEY = 'metadata';

    #[ORM\Column(type: 'uuid', unique: true, nullable: true)]
    private string $uuid;

    #[ORM\Column(length: 30, unique: true, nullable: false)]
    private string $route;

    #[ORM\Column(length: 60, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(length: 160, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $h1 = null;

    public function __construct()
    {
        $this->uuid = Uuid::generate();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getH1(): ?string
    {
        return $this->h1;
    }

    public function setH1(?string $h1): static
    {
        $this->h1 = $h1;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    public function setRoute(string $route): self
    {
        $this->route = $route;

        return $this;
    }
}
