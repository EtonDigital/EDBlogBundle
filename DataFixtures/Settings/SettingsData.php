<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 16.6.15.
 * Time: 09.31
 */

namespace ED\BlogBundle\DataFixtures\Settings;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use ED\BlogBundle\Model\Entity\BlogSettings;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SettingsData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    private $container;

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $settingsClass = $this->container->getParameter('blog_settings_class');

        $commentsSwitch = new $settingsClass();
        $commentsSwitch
            ->setProperty('comments_enabled')
            ->setValue(BlogSettings::COMMENTS_ENABLED);
        $manager->persist($commentsSwitch);

        $commentsPublicAccess = new $settingsClass();
        $commentsPublicAccess
            ->setProperty('comments_visible_public')
            ->setValue(BlogSettings::COMMENTS_PUBLIC_VISIBLE);

        $manager->persist($commentsPublicAccess);

        $commenterAccessLevel = new $settingsClass();
        $commenterAccessLevel
            ->setProperty('commenter_access_level')
            ->setValue(BlogSettings::COMMENTER_ACCESS_LEVEL_PUBLIC);

        $manager->persist($commenterAccessLevel);

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 1;
    }

    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }


}