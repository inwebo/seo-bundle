<?php

declare(strict_types=1);

namespace Inwebo\SeoBundle\Model;

use Inwebo\SeoBundle\Entity\Metadata;

interface MetadataInterface
{
    public function getId(): ?int;

    public function getUuid(): string;

    public function getH1(): ?string;

    public function setH1(?string $h1): void;

    public function getTitle(): ?string;

    public function setTitle(string $title): Metadata;

    public function getDescription(): ?string;

    public function setDescription(string $description): Metadata;

    public function getRoute(): string;

    public function setRoute(string $route): Metadata;
}
