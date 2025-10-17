<?php

declare(strict_types=1);

namespace Inwebo\SeoBundle;

use Doctrine\ORM\EntityManagerInterface;
use Inwebo\SeoBundle\Command\Breadcrumbs\ImportCommand as BaseBreadcrumbsImportCommand;
use Inwebo\SeoBundle\Command\Metadata\ImportCommand as BaseMetadataImportCommand;
use Inwebo\SeoBundle\Service\Bread;
use Inwebo\SeoBundle\Service\Breadcrumbs\Importer as BreadcrumbsImporter;
use Inwebo\SeoBundle\Service\Metadata;
use Inwebo\SeoBundle\Service\Metadata\Importer as MetadataImporter;
use Inwebo\SeoBundle\Twig\TwigFunctions;
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
                    ->arrayNode(Entity\Breadcrumb::CONFIG_KEY)
                        ->children()
                            ->scalarNode('entity')->cannotBeEmpty()->end()
                        ->end()
                    ->end()
                    ->arrayNode(Entity\Metadata::CONFIG_KEY)
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

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->extension('doctrine', [
            'orm' => [
                'mappings' => [
                    'InweboSeoBundle' => [
                        'is_bundle' => false,
                        'type' => 'attribute',
                        'dir' => '%kernel.project_dir%/vendor/inwebo/seo-bundle/src/Entity',
                        'prefix' => 'Inwebo\\SeoBundle\\Entity',
                        'alias' => 'InweboSeoBundle',
                    ],
                ],
            ],
        ]);
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->services()
            ->set('inwebo_seo.breadcrumbs_importer', BreadcrumbsImporter::class)
                ->arg('$router', service(RouterInterface::class))
                ->arg('$entityManager', service(EntityManagerInterface::class))
                ->arg('$entityFQCN', $config[Entity\Breadcrumb::CONFIG_KEY]['entity'])
                ->autowire(false)
                ->public()
        ;

        $container->services()
            ->set('inwebo_seo.metadata_importer', MetadataImporter::class)
                ->arg('$router', service(RouterInterface::class))
                ->arg('$entityManager', service(EntityManagerInterface::class))
                ->arg('$entityFQCN', $config[Entity\Metadata::CONFIG_KEY]['entity'])
                ->arg('$excludedRoutes', $config[Entity\Metadata::CONFIG_KEY]['excluded_routes'])
                ->autowire(false)
                ->public()
        ;

        $container->services()
            ->set('inwebo_seo.bread', Bread::class)
                ->arg('$environment', service(Environment::class))
                ->arg('$urlGenerator', service(UrlGeneratorInterface::class))
                ->arg('$entityManager', service(EntityManagerInterface::class))
                ->arg('$entityFQCN', $config[Entity\Breadcrumb::CONFIG_KEY]['entity'])
                ->tag('kernel.event_subscriber')
                ->autowire(false)
                ->public()
        ;

        $container->services()
            ->set('inwebo_seo.metadata', Metadata::class)
                ->arg('$environment', service(Environment::class))
                ->arg('$entityManager', service(EntityManagerInterface::class))
                ->arg('$entityFQCN', $config[Entity\Metadata::CONFIG_KEY]['entity'])
                ->arg('$excludedRoutes', $config[Entity\Metadata::CONFIG_KEY]['excluded_routes'])
                ->tag('kernel.event_subscriber')
                ->autowire(false)
                ->public()
        ;

        $container->services()
            ->set('inwebo_seo.command.importer.breadcrumbs', BaseBreadcrumbsImportCommand::class)
                ->arg('$importer', service('inwebo_seo.breadcrumbs_importer'))
                ->tag('console.command')
                ->autowire(false)
                ->public()
        ;

        $container->services()
            ->set('inwebo_seo.command.importer.metadata', BaseMetadataImportCommand::class)
                ->arg('$importer', service('inwebo_seo.metadata_importer'))
                ->tag('console.command')
                ->autowire(false)
                ->public()
        ;

        $container->services()
            ->set('inwebo_seo.twig.breadcrumbs', TwigFunctions::class)
            ->arg('$bread', service('inwebo_seo.bread'))
            ->arg('$metadata', service('inwebo_seo.metadata'))
            ->tag('twig.attribute_extension')
            ->tag('twig.runtime')
            ->autowire(false)
            ->public()
        ;
    }
}
