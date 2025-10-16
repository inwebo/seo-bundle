<?php

declare(strict_types=1);

namespace Inwebo\SeoBundle;

use Doctrine\ORM\EntityManagerInterface;
use Inwebo\SeoBundle\Service\Bread;
use Inwebo\SeoBundle\Service\Breadcrumbs\Importer as BreadcrumbsImporter;
use Inwebo\SeoBundle\Service\Metadata\Importer as MetadataImporter;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class InweboSeoBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition
            ->rootNode()
                ->children()
                    ->arrayNode('breadcrumb')
                        ->children()
                            ->scalarNode('entity')->cannotBeEmpty()->end()
                        ->end()
                    ->end()
                    ->arrayNode('metadata')
                        ->children()
                            ->scalarNode('entity')->cannotBeEmpty()->end()
                        ->end()
                        ->children()
                            ->arrayNode('excluded_routes')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->services()
            ->set(BreadcrumbsImporter::class)
            ->arg(0, service(RouterInterface::class))
            ->arg(1, service(EntityManagerInterface::class))
            ->arg(2, $config['breadcrumb']['entity'])
        ;

        $container->services()
            ->set(MetadataImporter::class)
            ->arg(0, service(RouterInterface::class))
            ->arg(1, service(EntityManagerInterface::class))
            ->arg(2, $config['metadata']['entity'])
            ->arg(3, $config['metadata']['excluded_routes'])
        ;


        $container->services()
            ->set('inwebo_seo.bread', Bread::class)
                ->arg('$environment', service(Environment::class))
                ->arg('$urlGenerator', service(UrlGeneratorInterface::class))
                ->arg('$entityManager', service(EntityManagerInterface::class))
                ->arg('$entityFQCN', $config['breadcrumb']['entity'])
                ->public()
        ;
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->extension('framework', [], prepend: true);
    }
}
