<?php

declare(strict_types=1);

namespace Inwebo\SeoBundle\Model;

interface MetadataInterface
{
    public function getId(): ?int;

    public function getUuid(): string;

    public function getH1(): ?string;

    public function setH1(?string $h1): MetadataInterface;

    public function getTitle(): ?string;

    public function setTitle(string $title): MetadataInterface;

    public function getDescription(): ?string;

    public function setDescription(string $description): MetadataInterface;

    public function getRoute(): string;

    public function setRoute(string $route): MetadataInterface;
}
