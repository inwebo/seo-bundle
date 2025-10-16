<?php

declare(strict_types=1);

namespace Inwebo\SeoBundle\Cache;

use Inwebo\SeoBundle\Service\Breadcrumbs\Importer as BreadcrumbsImporter;
use Inwebo\SeoBundle\Service\Metadata\Importer as MetadataImporter;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

readonly class SeoWarmer implements CacheWarmerInterface
{
    public function __construct(
        private BreadcrumbsImporter $breadcrumbsImporter,
        private MetadataImporter $metadataImporter,
    ) {
    }

    public function warmUp(string $cacheDir, ?string $buildDir = null): array
    {
        $this->breadcrumbsImporter->import();
        $this->metadataImporter->import();

        return [];
    }

    public function isOptional(): bool
    {
        return true;
    }
}
