<?php

declare(strict_types=1);

namespace Inwebo\SeoBundle\Tests\Fixtures\src;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;

trait RouterTrait
{
    public function getRouter(string $routesFile): RouterInterface
    {
        return new Router(new YamlFileLoader(new FileLocator(__DIR__.'/../../Fixtures/config')), $routesFile);
    }
}
