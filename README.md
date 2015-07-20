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
.. code-block:: bash

    $ composer require friendsofsymfony/user-bundle:"~2.0@dev" eko/feedbundle:dev-master ed/blog-bundle:dev-master@dev
