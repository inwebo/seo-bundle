# Inwebo SEO Bundle

![GitHub Actions Workflow Status](https://img.shields.io/github/actions/workflow/status/inwebo/seo-bundle/.github%2Fworkflows%2Flibrary.yml?branch=master&style=flat-square)
![Packagist Version](https://img.shields.io/packagist/v/inwebo/seo-bundle?style=flat-square)
![Packagist Downloads](https://img.shields.io/packagist/dd/inwebo/seo-bundle?style=flat-square)

A lightweight Symfony bundle to manage SEO metadata and breadcrumbs. It provides:

- Route-aware SEO metadata (title, description, H1) persisted via Doctrine ORM
- Twig-templated metadata values using controller arguments and a shared MetadataBag
- Breadcrumbs generation service
- Console commands to import routes into your persistence layer
- Cache warmer for precomputations

## Requirements

- PHP >= 8.4
- Symfony 7.3+
- Doctrine ORM 3+
- Twig 3.21+

See composer.json for the complete list of requirements.

## Installation

1. Install the package via Composer:

```
composer require inwebo/seo-bundle
```

2. Symfony Flex should auto-enable the bundle. If not, register it manually in config/bundles.php:

```php
return [
    // ...
    Inwebo\SeoBundle\InweboSeoBundle::class => ['all' => true],
];
```

## Configuration

Create config/packages/inwebo_seo.yaml with your entity FQCNs and optional exclusions:

```yaml
inwebo_seo:
  breadcrumb:
    entity: App\Entity\Breadcrumb            # must implement Inwebo\SeoBundle\Model\BreadcrumbInterface
  metadata:
    entity: App\Entity\Metadata              # must implement Inwebo\SeoBundle\Model\MetadataInterface
    excluded_routes:
      - _profiler
      - _wdt
```

The bundle wires services using these entries:

- inwebo_seo.bread: Breadcrumb service (event subscriber)
- inwebo_seo.metadata_importer: Metadata Importer
- inwebo_seo.breadcrumbs_importer: Breadcrumbs Importer

Refer to src/InweboSeoBundle.php for service wiring.

## Entities

You can use the provided entities as a reference:

- Inwebo\SeoBundle\Entity\Metadata implements Inwebo\SeoBundle\Model\MetadataInterface
- Inwebo\SeoBundle\Entity\Breadcrumb implements Inwebo\SeoBundle\Model\BreadcrumbInterface

Alternatively, implement the interfaces in your own entities and point the configuration to them.

## Usage

### Metadata rendering

The Metadata service listens to the controller arguments event and resolves the route-bound Metadata entity. Values are Twig-templated with controller arguments and the MetadataBag.

Example pattern stored in DB:

- title: "{{ title }}"
- description: "{{ description }}"
- h1: "{{ H1 }}"

At runtime, variables are resolved from:

1. Controller arguments (e.g., action parameters named title, description, H1)
2. Inwebo\SeoBundle\Model\MetadataBag global data

Programmatic access (e.g., from controllers or Twig extensions):

```php
use Inwebo\SeoBundle\Service\Metadata;

public function __construct(private Metadata $metadata) {}

public function __invoke(): Response
{
    $title = $this->metadata->getTitle();
    $description = $this->metadata->getDescription();
    $h1 = $this->metadata->getH1();
    // ...
}
```

You can enrich variables at runtime using the bag:

```php
use Inwebo\SeoBundle\Model\MetadataBag;

MetadataBag::create()::add(['description' => 'From bag']);
```

### Breadcrumbs

A Bread service is registered as an event subscriber to build breadcrumbs based on your entity and router. Inject and use it according to your app needs. See src/Service/Bread.php for details.

## Import commands

The bundle exposes console commands to import routes into your persistence layer. Ensure your entities are configured first.

- Metadata import: inwebo:seo:metadata:import
- Breadcrumbs import: inwebo:seo:breadcrumbs:import

These commands use the configured Importer services and Doctrine to create or update entries.

## Caching

A cache warmer (src/Cache/SeoWarmer.php) is available to pre-compute/cache SEO-related data at warmup time.

## Testing

- Run unit tests: `composer phpunit`
- Static analysis: `composer phpstan`
- Coding standards: `composer php-cs-fixer`

## License

GPL-3.0-or-later. See LICENSE file for details.

## Links

- Repository: https://github.com/inwebo/seo-bundle
