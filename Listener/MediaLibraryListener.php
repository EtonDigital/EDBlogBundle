<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 22.6.15.
 * Time: 08.47
 */

namespace ED\BlogBundle\Listener;

use Application\Sonata\MediaBundle\Entity\Gallery;
use Application\Sonata\MediaBundle\Entity\GalleryHasMedia;
use Doctrine\Bundle\DoctrineBundle\Registry;
use ED\BlogBundle\Event\MediaArrayEvent;
use ED\BlogBundle\Events\EDBlogEvents;
use Sonata\MediaBundle\Model\GalleryManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MediaLibraryListener implements EventSubscriberInterface
{
    protected $doctrine;
    protected $galleryManager;

    function __construct(Registry $doctrine, GalleryManagerInterface $galleryManager)
    {
        $this->doctrine = $doctrine;
        $this->galleryManager = $galleryManager;
    }


    public static function getSubscribedEvents()
    {
        return array(
            EDBlogEvents::ED_BLOG_MEDIA_LIBRARY_MEDIA_UPLOADED => "mediaUploaded"
        );
    }

    public function mediaUploaded(MediaArrayEvent $event)
    {
        $em = $this->doctrine->getManager();
        $mediaArray = $event->getMedia();
        $gallery = $this->galleryManager->findOneBy(array("name" => "Media Library"));

        if(!$gallery)
        {
            $gallery = new Gallery();
            $gallery->setName("Media Library");
            $gallery->setContext('default');
            $gallery->setDefaultFormat('big');
            $gallery->setEnabled(true);

            $em->persist($gallery);
        }

        foreach($mediaArray as $media)
        {
            $galleryHasMedia = new GalleryHasMedia();
            $galleryHasMedia->setMedia($media);
            $galleryHasMedia->setGallery($gallery);
            $galleryHasMedia->setEnabled(true);

            $em->persist($galleryHasMedia);
        }

        $em->flush();
    }
}