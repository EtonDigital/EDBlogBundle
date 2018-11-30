<?php

namespace ED\BlogBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class EDBlogExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
    }

    /**
     * Allow an extension to prepend the extension configurations.
     *
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');
        if (isset($bundles['DoctrineBundle'])) {
            // Get configuration of our own bundle
            $configs = $container->getExtensionConfig($this->getAlias());
            $config = $this->processConfiguration(new Configuration(), $configs);

            $edTargetEntities = array(
                'ED\BlogBundle\Interfaces\Model\BlogUserInterface' => $config['entities']['user_model_class'],
                'ED\BlogBundle\Interfaces\Model\BlogTermInterface' => $config['entities']['blog_term_class'],
                'ED\BlogBundle\Interfaces\Model\BlogTaxonomyInterface' => $config['entities']['blog_taxonomy_class'],
                'ED\BlogBundle\Interfaces\TaxonomyRelationsInterface' => $config['entities']['blog_taxonomy_relation_class'],
                'ED\BlogBundle\Interfaces\Model\ArticleInterface' => $config['entities']['article_class'],
                'ED\BlogBundle\Interfaces\Model\CommentInterface' => $config['entities']['blog_comment_class'],
                'ED\BlogBundle\Interfaces\Model\ArticleCommenterInterface' => $config['entities']['user_model_class'],
                'ED\BlogBundle\Interfaces\Model\ArticleMetaInterface' => $config['entities']['article_meta_class']
            );

            // Prepare for insertion
            $forInsertion = array(
                'orm' => array(
                    'resolve_target_entities' => $edTargetEntities,
                    'entity_managers' => array(
                        'default' => array(
                            'hydrators' => array(
                                'SettingsHydrator' => $config['hydrators']['settings_hydrator']
                            )
                        )
                    )
                ),
                'dbal' => array(
                    'types' => array(
                        'json' => 'Sonata\Doctrine\Types\JsonType'
                    )
                )
            );

            $container->setParameter('user_model_class', $config['entities']['user_model_class']);
            $container->setParameter('article_class', $config['entities']['article_class']);
            $container->setParameter('blog_comment_class', $config['entities']['blog_comment_class']);
            $container->setParameter('article_meta_class', $config['entities']['article_meta_class']);
            $container->setParameter('blog_settings_class', $config['entities']['blog_settings_class']);
            $container->setParameter('blog_taxonomy_class', $config['entities']['blog_taxonomy_class']);
            $container->setParameter('blog_term_class', $config['entities']['blog_term_class']);
            $container->setParameter('ed_blog.resolve_target_entities.config', $edTargetEntities);

        }

        if(isset($bundles['StofDoctrineExtensionsBundle'])) {
            $configs = $container->getExtensionConfig($this->getAlias());
            $config = $this->processConfiguration(new Configuration(), $configs);

            $stofConfigInsertation = array(
                'orm' => array(
                    'default' => array(
                        'sluggable' => true
                    )
                )
            );

        }


        foreach ($container->getExtensions() as $name => $extension) {
            switch ($name) {
                case 'doctrine':
                    if(isset($forInsertion)) {
                        $container->prependExtensionConfig($name, $forInsertion);
                    }
                    break;
                case 'stof_doctrine_extensions':
                    if(isset($stofConfigInsertation)){
                        $container->prependExtensionConfig($name, $stofConfigInsertation);
                    }
                    break;
            }
        }
    }

    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration();
    }


}
