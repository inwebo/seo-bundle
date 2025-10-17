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
    public function __construct(
        public string  $name {
            get {
                return $this->name;
            }
        },
        public string  $url {
            get {
                return $this->url;
            }
        },
        public ?string $title {
            get {
                return $this->title;
            }
        },
    ) {}
}
