EDBlogBundle
============

EDBlogBundle is extensive and user friendly blog bundle for Symfony2. It provides a lot of interesting features that makes a serious bloging platform from your symfony2 application. It is very intuitive and flexible, you can easily fit it to your own needs.

Features:
---------

* Blog admin panel
* User management, multiple roles Contributer, Author, Editor and Administrator
* Comments management
* Categories
* Tags
* Articles with multiple revisions, writing locks, autosave...
* Media gallery

Prerequisites
-------------

This bundle is relaying on many cool features provided by very popular Symfony2 bundles such as:
 * FOSUserBundle - for user management ( See https://github.com/FriendsOfSymfony/FOSUserBundle for more details)
 * KnpPaginatorBundle - Symfony 2 paginator ( See https://github.com/KnpLabs/KnpPaginatorBundle for more details )
 * StofDoctrineExtensionsBundle - DoctrineExtensions for Symfony2 ( See https://github.com/stof/StofDoctrineExtensionsBundle for more details )
 * SonataMediaBundle - Media management ( See https://github.com/sonata-project/SonataMediaBundle for more details)

Installation:
-------------

Installation process includes following steps:
 1. Composer vendors installation and activation
 2. SonataMediaBundle installation and configuration
 3. Creating blog related entities from provided model
 4. Rutes configuration
 5. Assetic configuration
 
Step 1: Composer vendors installation and activation
====================================================
If you already don't have composer installed, you can get it using:

    $ wget http://getcomposer.org/composer.phar

Then you can require following packages:
     
    $ composer require friendsofsymfony/user-bundle:"~2.0@dev" eko/feedbundle:dev-master ed/blog-bundle:dev-master@dev

Activate newly required bundles in AppKernel.php similar to this example:

    //app/Kernel.php
    class AppKernel extends Kernel
    {
        public function registerBundles()
        {
            $bundles = array(
                // ...
                new FOS\UserBundle\FOSUserBundle(),	
                new ED\BlogBundle\EDBlogBundle(),
                new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
                new Sonata\CoreBundle\SonataCoreBundle(),
                new Sonata\MediaBundle\SonataMediaBundle(),
                new Sonata\EasyExtendsBundle\SonataEasyExtendsBundle(),
                new JMS\SerializerBundle\JMSSerializerBundle(),
                new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
                new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
                new Eko\FeedBundle\EkoFeedBundle(),
                //new Application\Sonata\MediaBundle\ApplicationSonataMediaBundle(), //will be generated later	
            );

            // ...
        }
    }
    
Step 2: SonataMediaBundle installation and configuration
========================================================

Next, we will install and configure media management core. Please generate ApplicationSonataMediaBundle by running following code:
    
    $ php app/console sonata:easy-extends:generate --dest=src SonataMediaBundle
    
Now you can include ApplicationSonataMediaBundle in AppKernel by uncommenting or adding this line:

    //app/Kernel.php
    class AppKernel extends Kernel
    {
        public function registerBundles()
        {
            $bundles = array(
                // ...
		        new Application\Sonata\MediaBundle\ApplicationSonataMediaBundle(),	
		    );
            
            // ...
        }
    }
    
Add following configuration to your config.yml:

    //app/config/config.yml
    sonata_media:
        default_context: default
        db_driver: doctrine_orm # or doctrine_mongodb, doctrine_phpcr
        contexts:
        default:  # the default context is mandatory
            providers:
            - sonata.media.provider.dailymotion
            - sonata.media.provider.youtube
            - sonata.media.provider.image
            - sonata.media.provider.file

            formats:
            crop:  { width: 600 , quality: 80}
            small: { width: 100 , quality: 70}
            big:   { width: 500 , quality: 70}
            lib:   { width: 350 , height: 250 , quality: 70}
            excerpt:   { width: 780 , height: 500 , quality: 70}

        cdn:
        server:
            path: /uploads/media # http://media.sonata-project.org/

        filesystem:
        local:
            directory:  %kernel.root_dir%/../web/uploads/media
            create:     false
            
Finally we should create local directory for media storage:

    $ mkdir web/uploads
    $ mkdir web/uploads/media
    $ sudo chmod -R 0777 web/uploads
    
Step 3: Creating blog related entities from provided model
==========================================================

To be able to use EDBlog features you must implement certain entities somewhere inside your application. It will be very easy, only thing that you should do is to create relevant classes and extend our prepared models.

 3.1 Article entity:
 
    Create your article entity similar to this example:
     
        //src/Acme/DemoBundle/Entity/Article.php
        namespace Acme\Bundle\DemoBundle\Entity; 
        
        use ED\BlogBundle\Interfaces\Model\ArticleInterface;
        use ED\BlogBundle\Model\Entity\Article as BaseArticle;
        use Doctrine\ORM\Mapping as ORM;
    
        /**
         * @ORM\Table(name="acme_demo_article")
         * @ORM\HasLifecycleCallbacks
         * @ORM\Entity(repositoryClass="ED\BlogBundle\Model\Repository\ArticleRepository")
         */
        class Article extends BaseArticle implements ArticleInterface
        {
        }
        
 3.2 ArticleMeta entity:
 
    Create your article entity similar to this example:
        
        //src/Acme/DemoBundle/Entity/ArticleMeta.php
        namespace Acme\Bundle\DemoBundle\Entity; 
        
        use ED\BlogBundle\Interfaces\Model\ArticleMetaInterface;
        use ED\BlogBundle\Model\Entity\ArticleMeta as BaseArticleMeta;
        use Doctrine\ORM\Mapping as ORM;

        /**
         * @ORM\Table(name="acme_demo_article_meta")
         * @ORM\Entity()
         */
        class ArticleMeta extends BaseArticleMeta implements ArticleMetaInterface
        {
        }