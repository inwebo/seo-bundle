# Inwebo SEO Bundle

![GitHub Actions Workflow Status](https://img.shields.io/github/actions/workflow/status/inwebo/seo-bundle/.github%2Fworkflows%2Fbundle.yml?branch=master&style=flat-square)
![Packagist Version](https://img.shields.io/packagist/v/inwebo/seo-bundle?style=flat-square)
![Packagist Downloads](https://img.shields.io/packagist/dd/inwebo/seo-bundle?style=flat-square)

A lightweight Symfony bundle to manage SEO metadata (title, description, H1) and navigational breadcrumbs with Twig-powered templating and CLI importers.

## Contents
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configure your entities](#configure-your-entities)
- [Bundle configuration](#bundle-configuration)
- [Database/migrations](#databasemigrations)
- [Usage in Twig](#usage-in-twig)
- [Importing from routes (CLI)](#importing-from-routes-cli)
- [Route parameters and templating](#route-parameters-and-templating)
- [Global variables with Bags](#global-variables-with-bags)
- [Customizing the breadcrumbs template](#customizing-the-breadcrumbs-template)
- [Troubleshooting](#troubleshooting)
- [License](#license)

## Features
- SEO metadata service that renders title, meta description and H1 using Twig templates.
- Breadcrumbs service that renders a breadcrumb trail using a provided Twig partial.
- Both features read from your Doctrine entities; you control persistence in your app.
- Twig variables come from your controller arguments and from optional shared “bags”.
- Two console commands to import missing items from your Symfony routes.

## Requirements
- PHP >= 8.4
- Symfony 7.3 (FrameworkBundle, Console, Routing, TwigBundle)
- Doctrine ORM 3.x
- Twig 3.21+

## Installation
1) Install the package via Composer

```bash
composer require inwebo/seo-bundle
```

2) Symfony will auto-register the bundle (type: symfony-bundle). No manual steps needed.

## Configure your entities
This bundle ships mapped superclasses you should extend to create concrete Doctrine entities in your project.

Example: App entity for breadcrumbs

```php
<?php

namespace App\Entity\Seo;

use Doctrine\ORM\Mapping as ORM;
use Inwebo\SeoBundle\Entity\Breadcrumb as BaseBreadcrumb;

#[ORM\Entity]
#[ORM\Table(name: 'seo_breadcrumb')]
class Breadcrumb extends BaseBreadcrumb
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id = null;
}
```

Example: App entity for metadata

```php
<?php

namespace App\Entity\Seo;

use Doctrine\ORM\Mapping as ORM;
use Inwebo\SeoBundle\Entity\Metadata as BaseMetadata;

#[ORM\Entity]
#[ORM\Table(name: 'seo_metadata')]
class Metadata extends BaseMetadata
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id = null;
}
```

## Bundle configuration
Tell the bundle which FQCNs to use and optionally which routes to ignore for metadata resolution.

```yaml
# config/packages/inwebo_seo.yaml
inwebo_seo:
  breadcrumb:
    entity: App\Entity\Seo\Breadcrumb
  metadata:
    entity: App\Entity\Seo\Metadata
    excluded_routes:      # metadata resolution is skipped when route name starts with any of these prefixes
      - _                 # useful to ignore Symfony internals (e.g. _profiler, _wdt)
      - admin_            # example: skip your back-office
```

Notes
- Doctrine mapping for the bundle’s mapped superclasses is auto-prepended, so extending them is enough.
- You decide where and how to persist data (fixtures, admin UI, CLI importers below, etc.).

## Database/migrations
After creating your concrete entities, generate and run a migration in your app:

```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

## Usage in Twig
Drop these helpers into your base layout or individual templates:

```twig
<title>{{ inwebo_seo_title() }}</title>
<meta name="description" content="{{ inwebo_seo_description() }}" />
<h1>{{ inwebo_seo_h1() }}</h1>

{# Render the breadcrumb trail #}
{{ inwebo_seo_breadcrumbs() }}
```

## Importing from routes (CLI)
The bundle provides commands that scan your router and create missing records so you can fill them later.

```bash
# Import missing breadcrumbs for known routes
php bin/console inwebo_seo:import:breadcrumbs   # alias: is:i:b

# Import missing metadata for known routes
php bin/console inwebo_seo:import:metadata     # alias: is:i:m
```

- Each command prints a table of newly persisted rows or a message if nothing is needed.
- You can safely run these commands multiple times; they only create missing items.

## Route parameters and templating
- All breadcrumb and metadata string fields (e.g., breadcrumb name/title, metadata title/description/h1) are rendered using Twig.
- Available Twig variables include:
  - Controller arguments for the current request (by name)
  - Values stored in the corresponding Bag (see below)
- You can reference route parameters by name in your templates. Example:
  - Breadcrumb.name: `Category: {{ category.name }}`
  - Breadcrumb.title: `Back to {{ category.name }}`
  - Metadata.title: `{{ product.name }} | My Shop`

## Global variables with Bags
Sometimes you want to provide extra variables not present in controller arguments. Use the provided Bags.

- `MetadataBag` provides variables for metadata templates
- `BreadcrumbBag` provides variables for breadcrumb templates

Add values anywhere before rendering (e.g., in a listener or controller):

```php
<?php
use Inwebo\SeoBundle\Model\MetadataBag;
use Inwebo\SeoBundle\Model\BreadcrumbBag;

MetadataBag::create()::add(['siteName' => 'Acme']);
BreadcrumbBag::create()::add(['locale' => 'en']);
```

Then in your templates stored in DB you can do:
- `{{ siteName }}`
- `{{ locale }}`

## Customizing the breadcrumbs template
The breadcrumbs HTML is rendered via the template path: `@InweboSeo/_breadcrumbs.html.twig`

Options to customize:
- Easiest: copy the file from `vendor/inwebo/seo-bundle/templates/_breadcrumbs.html.twig` into your app and add a Twig path with higher priority for the `InweboSeo` namespace, or override via the standard bundles override path if configured.
- Or, keep the function and style via CSS.

## Troubleshooting
- I see an exception like "<route> is not a valid metadata object."
  - Ensure you created and configured your concrete `Metadata` entity and that a row exists for the current route.
  - Check `inwebo_seo.metadata.entity` is set to your FQCN and that the route name matches.
- Breadcrumbs don’t show up:
  - Ensure a `Breadcrumb` row exists for the current route. Use the importer to create missing ones.
  - If you need parameters, set `routeParameters` on the breadcrumb so URLs can be generated.
- Metadata not applied on some routes:
  - Use `excluded_routes` config to skip areas like `_profiler`, `admin_`, etc.

## License
GPL-3.0-or-later. See LICENSE file for details.
