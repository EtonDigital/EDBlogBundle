<?php
/**
 * Created by Eton Digital.
 * User: Milos Milojevic (milos.milojevic@etondigital.com)
 * Date: 5/21/15
 * Time: 12:01 PM
 */

namespace ED\BlogBundle\Controller\Backend;

use Application\Sonata\MediaBundle\Entity\Media;
use Doctrine\Common\Collections\ArrayCollection;
use ED\BlogBundle\Event\MediaArrayEvent;
use ED\BlogBundle\Events\EDBlogEvents;
use ED\BlogBundle\Forms\ArticlePhotoType;
use ED\BlogBundle\Forms\MediaInfoType;
use ED\BlogBundle\Forms\MediaType;
use ED\BlogBundle\Handler\Pagination;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class MediaController extends DefaultController
{
    /**
     * @Route("/media/list", name="ed_blog_admin_media_list")
     */
    public function mediaListAction(Request $request)
    {
        $user = $this->getBlogUser();

        $formMedia = $this->createForm(ArticlePhotoType::class);

        $paramsForTwig = array('form_media' => $formMedia->createView());
        $paginator = $this->get('ed_blog.paginator');
        $response = $this->getPaginated($paginator, $paramsForTwig);

        return $response;
    }

    private function getPaginated($paginator, $paramsForTwig = array(), $ajaxParams = array())
    {
        $mediaManager = $this->container->get('sonata.media.manager.media');

        $queryBuilder = $mediaManager->getObjectManager()->getRepository($mediaManager->getClass())->createQueryBuilder('m')
            ->select('m')
            ->innerJoin('m.galleryHasMedias', 'hasMedias')
            ->innerJoin('hasMedias.gallery','g')
            ->where('g.name= :mediaGallery')
            ->andWhere('hasMedias.enabled = 1')
            ->andWhere('m.enabled = 1')
            ->orderBy('m.id' , 'DESC')
            ->setParameter('mediaGallery', 'Media Library');

        $response = $paginator->paginate(
            $queryBuilder,
            'EDBlogBundle:Media:list',
            'EDBlogBundle:Global:paginationClassic',
            $paramsForTwig,
            Pagination::DOZEN,
            null,
            'EDBlogBundle:Global:paginationClassic.html.twig',
            $ajaxParams,
            'ed_blog_admin_media_list'
        );

        return $response;
    }

    /**
     * @Route("/media/upload", name="ed_blog_admin_media_upload")
     */
    public function uploadAction(Request $request)
    {
        $user = $this->getBlogUser();
        $mediaManager = $this->container->get('sonata.media.manager.media');

        $form = $this->createForm(ArticlePhotoType::class);

        if ($request->getMethod() == 'POST')
        {
            $form->handleRequest($request);

            $dispatcher = $this->get('event_dispatcher');
            $attachment = $form['media']->getData();

            //to support single file upload (multiple=false)
            if (!is_array($attachment))
            {
                $attachment = array($attachment);
            }

            $mediaArray = new ArrayCollection();
            $errorArray = array();

            foreach ($attachment as $attached)
            {
                if(in_array($attached->getMimeType(), array(
                    'image/pjpeg', 'image/jpeg', 'image/png', 'image/x-png', 'image/gif',
                )))
                {
                    try {
                        $media = new Media();

                        $media->setBinaryContent($attached);
                        $media->setContext('default');
                        $media->setProviderName('sonata.media.provider.image');
                        $media->setName($attached->getClientOriginalName());
                        $media->setEnabled(true);

                        $mediaManager->save($media);
                        $mediaArray->add($media);
                    }
                    catch(\Exception $e)
                    {
                        $errorArray[] = array(
                            "name" => $attached->getClientOriginalName(),
                            "message" => $e->getMessage()
                        );
                    }
                }
                else
                {
                    $errorArray[] = array(
                        "name" => $attached->getClientOriginalName(),
                        "message" => "The file is unsupported");
                }
            }



            if(count($mediaArray))
            {
                $dispatcher->dispatch(EDBlogEvents::ED_BLOG_MEDIA_LIBRARY_MEDIA_UPLOADED, new MediaArrayEvent($mediaArray));
            }
        }

        $paginator = $this->get('ed_blog.paginator');
        $response = $this->getPaginated($paginator, array('form' => $form->createView()),  array('errors' => $errorArray));

        return $response;
    }

    /**
     * @Route("/media/upload/excerpt", name="ed_blog_admin_media_upload_excerpt")
     */
    public function uploadExcerptAction(Request $request)
    {
        $user = $this->getBlogUser();
        $mediaManager = $this->container->get('sonata.media.manager.media');
        $builder = $this->createFormBuilder();

        $form = $builder->add('excerptImage', FileType::class,
            array('required' => false)
        )->getForm();

        if ($request->getMethod() == 'POST')
        {
            $form->handleRequest($request);
            $attachment = $form['excerptImage']->getData();

            $media = new Media();
            $media->setBinaryContent($attachment);
            $media->setContext('default');
            $media->setProviderName('sonata.media.provider.image');
            $media->setName($attachment->getClientOriginalName());

            $mediaManager->save($media);

            return new JsonResponse(array('success' => 'true', 'id' => $media->getId(), 'href' => $this->generateUrl('ed_blog_admin_media_delete', array(
                'id' => $media->getId())),
                'media' => $this->renderView('EDBlogBundle:Media:image.html.twig', array('media' => $media))));
        }

        return new JsonResponse(array('success' => 'false'));
    }

    /**
     * Will remove Media from MediaLibrary, but not from file system!
     *
     * @Route("/media/delete/{id}", name="ed_blog_admin_media_delete")
     * @ParamConverter("media", class="ApplicationSonataMediaBundle:Media")
     */
    public function deleteAction(Request $request, Media $media)
    {
        $user = $this->getBlogUser();
        $mediaLibrary = $this->container->get('sonata.media.manager.gallery')->findOneBy(array('name' => 'Media Library'));
        $em = $this->getDoctrine()->getManager();

        foreach($media->getGalleryHasMedias() as $hasMedia)
        {
            if($hasMedia->getGallery() == $mediaLibrary)
            {
                $em->remove($hasMedia);
            }
        }

        $em->flush();

        if($request->isXmlHttpRequest())
        {
            return new JsonResponse(array('success' => 'true'));
        }

        $this->get('session')->getFlashBag()->add('success', 'Photo removed successfully.');
        return $this->redirectToRoute('ed_blog_admin_media_list');
    }

    /**
     * @Route("/media/edit-info/{id}", name="ed_blog_admin_media_edit_info")
     * @ParamConverter("media", class="ApplicationSonataMediaBundle:Media")
     */
    public function editInfoAction(Request $request, Media $media)
    {
        $user = $this->getBlogUser();

        $form = $this->createForm(MediaInfoType::class, array("description" => $media->getDescription()));

        if($request->isMethod('POST'))
        {
            $form->handleRequest($request);

            if ($form->isValid())
            {
                $media->setDescription($form['description']->getData());
                $em = $this->getDoctrine()->getManager();

                $em->persist($media);
                $em->flush();
            }

            if ($request->isXmlHttpRequest())
            {
                return new JsonResponse(array('success' => true));
            }
            else
            {
                $this->get('session')->getFlashBag()->add('success', 'Photo details updated successfully.');
                return $this->redirectToRoute('ed_blog_admin_media_list');
            }
        }

        return $this->render("EDBlogBundle:Media:editInfoForm.html.twig", array(
            "id" => $media->getId(),
            "form" => $form->createView()
        ));
    }

    /**
     * @Route("/media/edit/{id}", name="ed_blog_admin_media_edit")
     * @ParamConverter("media", class="ApplicationSonataMediaBundle:Media")
     */
    public function editAction(Request $request, $media)
    {
        $user = $this->getBlogUser();
        $form = $this->createForm(MediaType::class, array(
            'name' => $media->getName(),
            'description' => $media->getDescription()
        ));

        if($request->isMethod('post'))
        {
            $form->handleRequest($request);

            if($form->isValid())
            {
                $media->setName( $form['title']->getData() . '.' . $form['extension']->getData()  );
                $media->setDescription( $form['description']->getData() );

                $em = $this->getDoctrine()->getManager();

                $em->persist($media);
                $em->flush();
            }
        }

        if($request->isXmlHttpRequest())
        {
            return new JsonResponse(array(
                "success" => true,
                "html" => $this->renderView('@EDBlog/Media/editForm.html.twig', array(
                    'media' => $media,
                    'form' => $form->createView()
                ))
            ));
        }
        else
        {
            return $this->render("@EDBlog/Media/edit.html.twig", array(
                'media' => $media,
                'form' => $form->createView()
            ));
        }

    }

    /**
     * @Route("/media/crop/{id}", name="ed_blog_admin_media_crop")
     * @ParamConverter("media", class="ApplicationSonataMediaBundle:Media")
     */
    public function cropAction(Request $request, $media)
    {
        $user = $this->getBlogUser();
        $mediaManager = $this->get('sonata.media.manager.media');

        $x = intval( $request->get('x', 0) );
        $y = intval( $request->get('y', 0) );
        $width = intval( $request->get('w', 0) );
        $height = intval( $request->get('h', 0) );
        $boxWidth = intval($request->get('box-width', 0));
        $origWidth = $media->getWidth();
        $origHeight = $media->getHeight();
        $origFile = $this->get('kernel')->getRootDir() . '/../web' .  $this->container->get( $media->getProviderName() )->generatePublicUrl($media, 'reference');
        $genaratedFile = $this->get('ed_media_handler')->generateNewFileName($origFile);

        if(!$width || !$height || !$origWidth || !$origHeight)
        {
            return new JsonResponse(array(
                'success' => false,
                'message' => 'Please select something.'
            ));
        }

        //calculate real coordinates and dimensions
        $boxHeight = round( ($boxWidth/$origWidth) * $origHeight);
        $widthRatio = $origWidth / $boxWidth;
        $heightRatio = $origHeight / $boxHeight;
        $realWidth = intval( round($width * $widthRatio, 0) );
        $realHeight = intval( round($height * $heightRatio, 0) );
        $realX = intval( round($x * $widthRatio) );
        $realY = intval( round($y * $heightRatio) );

        //create a new image from selection area
        $resource = false;
        $newResource = ImageCreateTrueColor($realWidth, $realHeight);

        switch ($media->getContentType())
        {
            case 'image/png':
                $resource = imagecreatefrompng($origFile);
                imagecopyresampled($newResource, $resource, 0, 0, $realX, $realY, $realWidth, $realHeight, $realWidth, $realHeight);
                $created = imagepng($newResource, $genaratedFile, 9);
                break;
            case 'image/gif':
                $resource = imagecreatefromgif($origFile);
                imagecopyresampled($newResource, $resource, 0, 0, $realX, $realY, $realWidth, $realHeight, $realWidth, $realHeight);
                $created = imagegif($newResource, $genaratedFile, 100);
                break;
            case 'image/jpeg':
                $resource = imagecreatefromjpeg($origFile);
                imagecopyresampled($newResource, $resource, 0, 0, $realX, $realY, $realWidth, $realHeight, $realWidth, $realHeight);
                $created = imagejpeg($newResource, $genaratedFile, 100);
                break;
        }

        if(!$resource)
        {
            return new JsonResponse(array(
                'success' => false,
                'message' => 'Sorry, this file format is unsupported.'
            ));
        }

        //create Media object from generated file
        $mediaNew = $this->get('ed_media_handler')->generateMediaFromFile($genaratedFile);
        $mediaManager->save($mediaNew);

        //Add media to Media Library
        $dispatcher = $this->get('event_dispatcher');
        $dispatcher->dispatch( EDBlogEvents::ED_BLOG_MEDIA_LIBRARY_MEDIA_UPLOADED, new MediaArrayEvent( array($mediaNew) ) );

        //finally generate thumbs
        $this->get( $media->getProviderName() )->generateThumbnails($media);

        return new JsonResponse( array(
            'success' => true,
            'html' => $this->renderView('@EDBlog/Media/cropPanel.html.twig', array(
                'media' => $mediaNew
            )),
            'redirectUrl' => $this->generateUrl('ed_blog_admin_media_edit', array('id' => $mediaNew->getId()), true),
            'message' => 'You will be redirected to your photo...'
        ) );
    }
}