<?php

declare(strict_types=1);

namespace Inwebo\SeoBundle\Twig;

use Inwebo\SeoBundle\Service\Bread;
use Inwebo\SeoBundle\Service\Metadata;
use Twig\Attribute\AsTwigFunction;
use Twig\Markup;

readonly class TwigFunctions
{
    public function __construct(
        private Bread $bread,
        private Metadata $metadata,
    ) {
    }

    #[AsTwigFunction('inwebo_seo_breadcrumbs')]
    public function breadcrumbs(): Markup
    {
        return new Markup($this->bread->crumbs(), 'UTF-8');
    }

    #[AsTwigFunction('inwebo_seo_h1')]
    public function h1(): Markup
    {
        return new Markup($this->metadata->getH1(), 'UTF-8');
    }

    #[AsTwigFunction('inwebo_seo_title')]
    public function title(): Markup
    {
        return new Markup($this->metadata->getTitle(), 'UTF-8');
    }

    #[AsTwigFunction('inwebo_seo_description')]
    public function description(): Markup
    {
        return new Markup($this->metadata->getDescription(), 'UTF-8');
    }
}
