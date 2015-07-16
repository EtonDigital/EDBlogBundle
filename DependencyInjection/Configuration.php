<?php

namespace ED\BlogBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ed_blog');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        $rootNode
            ->children()
                ->arrayNode('entities')
                    ->children()
                        ->scalarNode('user_model_class')->isRequired()->end()
                        ->scalarNode('article_class')->isRequired()->end()
                        ->scalarNode('article_meta_class')->isRequired()->end()
                        ->scalarNode('blog_term_class')->isRequired()->end()
                        ->scalarNode('blog_taxonomy_class')->isRequired()->end()
                        ->scalarNode('blog_taxonomy_relation_class')->isRequired()->end()
                        ->scalarNode('blog_comment_class')->isRequired()->end()
                        ->scalarNode('blog_settings_class')->isRequired()->end()
                    ->end()
                ->end() // entities
                ->arrayNode('hydrators')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('settings_hydrator')->defaultValue('\ED\BlogBundle\Hydrators\SettingsHydrator')->end()
                    ->end()
                 ->end() //hydrators
            ->end()
        ;

        return $treeBuilder;
    }
}
