<?php

declare(strict_types=1);

namespace Inwebo\SeoBundle\Model\Dto;

final class Breadcrumb
{
    /**
     * @param string      $name  Human-readable label to display
     * @param string      $url   Absolute URL for the breadcrumb
     * @param string|null $title Optional SEO title
     */
    public function __construct(private string $name, private string $url, private ?string $title = null)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }
}
