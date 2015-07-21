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

License:
--------

This bundle is under the MIT license.

 ```
 Resources/meta/LICENSE
 ```

Prerequisites
-------------

This bundle is relaying on many cool features provided by very popular Symfony2 bundles such as:
 * **FOSUserBundle** - for user management ( See https://github.com/FriendsOfSymfony/FOSUserBundle for more details)
 * **KnpPaginatorBundle** - Symfony 2 paginator ( See https://github.com/KnpLabs/KnpPaginatorBundle for more details )
 * **StofDoctrineExtensionsBundle** - DoctrineExtensions for Symfony2 ( See https://github.com/stof/StofDoctrineExtensionsBundle for more details )
 * **SonataMediaBundle** - Media management ( See https://github.com/sonata-project/SonataMediaBundle for more details)

Installation:
-------------

Installation process includes following steps:
 1. Composer vendors installation and activation
 2. SonataMediaBundle installation and configuration
 3. Creating blog related entities from provided model
 4. EDBlogBundle configuration
 5. Rutes configuration
 6. Assetic configuration
 7. Finish
 
Step 1: Composer vendors installation and activation
====================================================
If you already don't have composer installed, you can get it using:

    $ wget http://getcomposer.org/composer.phar

Then you can require following packages:
     
    $ composer require friendsofsymfony/user-bundle:"~2.0@dev" eko/feedbundle:dev-master ed/blog-bundle:dev-master@dev

Activate newly required bundles in AppKernel.php similar to this example:

```php
<?php
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
```
    
Step 2: SonataMediaBundle installation and configuration
========================================================

Next, we will install and configure media management core. Please generate ApplicationSonataMediaBundle by running following code:
    
    $ php app/console sonata:easy-extends:generate --dest=src SonataMediaBundle
    
Now you can include ApplicationSonataMediaBundle in ``app/AppKernel.php`` by uncommenting or adding this line:

```php
<?php
//app/Kernel.php

//...
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
```    
Add following configuration to your config.yml:

```yml
 # app/config/config.yml

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
```    
            
Finally we should create local directory for media storage:

    $ mkdir web/uploads
    $ mkdir web/uploads/media
    $ sudo chmod -R 0777 web/uploads
    
Step 3: Creating blog related entities from provided model
==========================================================

To be able to use EDBlogBundle features you must implement certain entities somewhere inside your application. It will be very easy, only thing that you should do is to create relevant classes and extend our prepared models.

###3.1 Article entity

Create your Article entity similar to this example:
 
```php
<?php     
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
```
        
###3.2 ArticleMeta entity

Create your ArticleMeta entity similar to this example:
  
```php            
<?php        
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
```
            
###3.3 Comment entity

Create your Comment entity similar to this example:

```php
<?php
//src/Acme/DemoBundle/Entity/Comment.php

namespace Acme\Bundle\DemoBundle\Entity; 

use ED\BlogBundle\Interfaces\Model\CommentInterface;
use ED\BlogBundle\Model\Entity\Comment as BaseComment;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="acme_demo_comment")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="ED\BlogBundle\Model\Repository\CommentRepository")
 */
class Comment extends BaseComment implements CommentInterface
{
}            
```

###3.4 Settings entity

Create your Settings entity similar to this example:

```php
<?php
//src/Acme/DemoBundle/Entity/Settings.php

namespace Acme\Bundle\DemoBundle\Entity; 

use ED\BlogBundle\Interfaces\Model\BlogSettingsInterface;
use ED\BlogBundle\Model\Entity\BlogSettings as BaseSettings;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="acme_demo_settings")
 * @ORM\Entity(repositoryClass="ED\BlogBundle\Model\Repository\BlogSettingsRepository")
 */
class Settings extends BaseSettings implements BlogSettingsInterface
{
}
```

###3.5 Taxonomy entity

Create your Taxonomy entity similar to this example:

```php
<?php
//src/Acme/DemoBundle/Entity/Taxonomy.php

namespace Acme\Bundle\DemoBundle\Entity; 

use ED\BlogBundle\Interfaces\Model\BlogTaxonomyInterface;
use ED\BlogBundle\Model\Entity\Taxonomy as BaseTaxonomy;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="acme_demo_taxonomy")
 * @ORM\Entity(repositoryClass="ED\BlogBundle\Model\Repository\TaxonomyRepository")
 */
class Taxonomy extends BaseTaxonomy implements BlogTaxonomyInterface
{
}
```

###3.5 TaxonomyRelation entity

Create your TaxonomyRelation entity similar to this example:

```php
<?php
//src/Acme/DemoBundle/Entity/TaxonomyRelation.php

namespace Acme\Bundle\DemoBundle\Entity; 

use ED\BlogBundle\Interfaces\Model\TaxonomyRelationInterface;
use ED\BlogBundle\Model\Entity\TaxonomyRelation as BaseTaxonomyRelation;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="acme_demo_taxonomy_relation")
 * @ORM\Entity()
 */
class TaxonomyRelation extends BaseTaxonomyRelation implements TaxonomyRelationInterface
{
}
```

###3.6 Term entity

Create your Term entity similar to this example:

```php
<?php
//src/Acme/DemoBundle/Entity/Term.php

namespace Acme\Bundle\DemoBundle\Entity; 

use ED\BlogBundle\Interfaces\Model\BlogTermInterface;
use ED\BlogBundle\Model\Entity\Term as BaseTerm;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="acme_demo_term")
 * @ORM\Entity()
 * @UniqueEntity("slug")
 */
class Term extends BaseTerm implements BlogTermInterface
{
}
```

###3.7 User entity

To be able to use EDBlogBundle your User entity should implement two interfaces: BlogUserInterface and ArticleCommenterInterface. Modify your User entity something similar to the following example.

**Note:**  

> Find more about FOSUser integration on https://github.com/FriendsOfSymfony/FOSUserBundle/blob/1.2.0/Resources/doc/index.md

```php
<?php
//src/AppBundle/Entity/User

namespace Acme\Bundle\DemoBundle\Entity; 

use ED\BlogBundle\Interfaces\Model\BlogUserInterface;
use ED\BlogBundle\Interfaces\Model\ArticleCommenterInterface;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 */
class User extends BaseUser implements BlogUserInterface, ArticleCommenterInterface
{
    /**
     * Required by BlogUserInterface
     *
     * @ORM\Column(name="blog_display_name", type="string")
     */
    protected $blogDisplayName;

    public function getBlogDisplayName()
    {
        return $this->blogDisplayName;
    }
    
    public function setBlogDisplayName($blogDisplayName)
    {
        $this->blogDisplayName = $blogDisplayName;
    
        return $this;
    }
}
```

###3.8 User Repository

Your User repository class should implement BlogUserRepositoryInterface. We prepared ``ED\BlogBundle\Model\Repository\UserRepository`` that you can use as a start point. Modify your UserRepository class something similar to:

```php
<?php
//src/AppBundle/Repository/UserRepository

namespace AppBundle\Repository; 

use ED\BlogBundle\Interfaces\Repository\BlogUserRepositoryInterface;
use ED\BlogBundle\Model\Repository\UserRepository as BaseUserRepository;

class AppUserRepository extends BaseUserRepository implements BlogUserRepositoryInterface
{
}
```

Step 4: EDBlogBundle configuration
=================================

Now when your entities are ready, you can configure EDBlogBundle in your ``app/config/config.yml``. Please add `ed_blog` to your configuration while targeting previously created entities, something similar to the following example:

```yml
 # app/config/config.yml
 
# ...
ed_blog:
    entities:
        user_model_class: AppBundle\Entity\User
        article_class: Acme\Bundle\DemoBundle\Entity\Article
        article_meta_class: Acme\Bundle\DemoBundle\Entity\ArticleMeta
        blog_term_class: Acme\Bundle\DemoBundle\Entity\Term
        blog_taxonomy_class: Acme\Bundle\DemoBundle\Entity\Taxonomy
        blog_taxonomy_relation_class: Acme\Bundle\DemoBundle\Entity\TaxonomyRelation
        blog_comment_class: Acme\Bundle\DemoBundle\Entity\Comment
        blog_settings_class: Acme\Bundle\DemoBundle\Entity\Settings
```

Step 5: Rutes configuration
===========================

Enable EDBlogBundle and SonataMediaBundle rutes by adding following code to your ``app/config/routing.yml``:

```yml
 # app/config/routing.yml
 
gallery:
    resource: '@SonataMediaBundle/Resources/config/routing/gallery.xml'
    prefix: /media/gallery

media:
    resource: '@SonataMediaBundle/Resources/config/routing/media.xml'
    prefix: /media

ed_blog_admin_feed:
    path:      /feed/{type}
    defaults:  { _controller: EDBlogBundle:Feed:feed }

ed_blog_frontend:
    resource: "@EDBlogBundle/Controller/Frontend/"
    type:     annotation
    prefix:   /

ed_blog:
    resource: "@EDBlogBundle/Controller/Backend/"
    type:     annotation
    prefix:   /blog/admin/
```
Step 6: Assetic configuration
=============================

Add EDBlogBundle to your Assetic configuration similar to:

```yml
# app/config/config.yml

#...
assetic:
    # ...
    bundles:    [ EDBlogBundle ]
```

Step 7: Finish
==============

Now you are ready to finish your EDBlogBundle installation:

    $ php app/console as:in --symlink
    $ php app/console as:du --env=prod
    $ php app/console doc:sc:update --force
    
Before you can access Blog administration area you should promote a Blog Administartor. In order to that you should assign two roles to your future blog administrator user ``ROLE_BLOG_USER`` and ``ROLE_BLOG_ADMIN``. You can do it easily by modifying your registration action or by running following code from the console:
 
    $ php app/console fos:user:promote

**Note:** 

> Every blog user must have role ``ROLE_BLOG_USER`` assigned. Beside this one and according to permission level they should have one of following:
> * **ROLE_BLOG_ADMIN** - Administrators can see/access/modify: Articles, Users, Categories, Tags, Comments, Media library, Settings
> * **ROLE_BLOG_EDITOR** - Editors can see/access: Articles, Comments, Media library
> * **ROLE_BLOG_AUTHOR** - Authors can see/access: Articles, Media library, can publish and manage their own posts
> * **ROLE_BLOG_CONTRIBUTOR** - Contributors can see/access: Articles, Media library, can write and manage their own posts but cannot publish them

Now you can login as a blog administrator and visit `/blog/admin`. Please save your initial blog settings first on `/blog/admin/settings/edit`.

Congratulation! Your EDBlogBundle is ready. 

Please tell us what you think. 

Enjoy using EDBlogBundle and don't forget to contribute!

