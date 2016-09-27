<?php
/**
 * Created by Eton Digital.
 * User: Milos Milojevic (milos.milojevic@etondigital.com)
 * Date: 5/11/15
 * Time: 10:50 AM
 */

namespace ED\BlogBundle\Controller\Backend;

use Application\Sonata\MediaBundle\Entity\Media;
use Doctrine\Common\Collections\ArrayCollection;
use ED\BlogBundle\Event\ArticleAdministrationEvent;
use ED\BlogBundle\Event\MediaArrayEvent;
use ED\BlogBundle\Event\TaxonomyArrayEvent;
use ED\BlogBundle\Events\EDBlogEvents;
use ED\BlogBundle\Forms\ArticleExcerptType;
use ED\BlogBundle\Forms\ArticlePhotoType;
use ED\BlogBundle\Handler\Pagination;
use ED\BlogBundle\Model\Entity\Article;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ArticleController extends DefaultController
{
    /**
     * @Route("/article/create", name="ed_blog_admin_article_create")
     */
    public function createAction(Request $request)
    {
        $user = $this->getBlogUser();
        $mediaManager = $this->get('sonata.media.manager.media');
        $categoryRepository = $this->get('app_repository_taxonomy');

        $draft = $this->get('article_generator')->getObject();
        $draft->setAuthor($user);

        $formMedia = $this->createForm(new ArticlePhotoType());
        $formExcerptMedia = $this->createForm(new ArticleExcerptType());
        $form = $this->createForm('article', $draft);

        if ($request->getMethod() == 'POST')
        {
            $form->handleRequest($request);

            if($form->isValid())
            {
                $em = $this->getDoctrine()->getManager();
                $draft = $form->getData();

                //Permission is needed to change author from default user
                if(!$this->get('security.context')->isGranted('SWITCH_ARTICLE_AUTHOR'))
                {
                    $draft
                        ->setAuthor($user);
                }

                if ($this->get('security.context')->isGranted('PUBLISH_ARTICLE', $draft) && $form->get('publish')->isClicked())
                {
                    $article = $this->get('article_generator')->generateNewArticleFromDraft($draft);

                    $article
                        ->setPublishedAt(new \DateTime());

                    $em->persist($article);
                    $em->flush();

                    //we need this $article ID and different slug
                    $draft
                        ->setParent($article)
                        ->setSlug('drafted-' . $draft->getParent()->getId());

                }

                $draft
                    ->setStatus(Article::STATUS_DRAFTED);

                $em->persist($draft);
                $em->flush();

                //must be after flush
                $dispacher = $this->get('event_dispatcher');
                $taxonomies = array_merge( $draft->getCategories()->toArray(), $draft->getTags()->toArray() );

                $dispacher->dispatch(EDBlogEvents::ED_BLOG_ARTICLE_CREATED, new TaxonomyArrayEvent( $taxonomies ));


                return $this->redirectToRoute('ed_blog_admin_article_edit', array(
                    'slug' => $draft->getParent() ? $draft->getParent()->getSlug() : $draft->getSlug()
                ));
            }
        }

        return $this->render("EDBlogBundle:Article:create.html.twig",
            array(
                'form' => $form->createView(),
                'form_media' => $formMedia->createView(),
                'form_excerpt_media' => $formExcerptMedia->createView(),
                'medias' => $mediaManager->findAll(),
                'categories' => $categoryRepository->getAllCategories(),
            ));
    }

    /**
     * @Route("/article/edit/{slug}", name="ed_blog_admin_article_edit")
     * @ParamConverter("article", class="ED\BlogBundle\Interfaces\Model\ArticleInterface", converter="abstract_converter")
     */
    public function editAction(Request $request, $article)
    {
        $user = $this->getBlogUser();

        if($this->get('security.context')->isGranted('EDIT_ARTICLE', $article) === false)
        {
            throw new AccessDeniedException('This content is currently unavailable. It may be temporarily unavailable, the link you clicked on may have expired, or you may not have permission to view this page.');
        }

        //get writing lock
        $isWritingLocked = $this->get('ed_blog.handler.article_handler')->isLocked($user, $article);

        //get latest drafted version of the article
        $latestDraft = $this->get('app_repository_article')->findOneBy(array(
            "status" => Article::STATUS_DRAFTED,
            "parent" => $article
        ), array(
            "modifiedAt" => "DESC",
            "id" => "DESC"
        ));

        $draft = $this->get('article_generator')->generateNewDraftFromLatestRevision($latestDraft, $article);
        $mediaManager = $this->container->get('sonata.media.manager.media');
        $dispacher = $this->get('event_dispatcher');
        $dispacher->dispatch(EDBlogEvents::ED_BLOG_ARTICLE_PREUPDATE_INIT, new ArticleAdministrationEvent($article));

        $formMedia = $this->createForm(new ArticlePhotoType());
        $formExcerptMedia = $this->createForm(new ArticleExcerptType());

        $form = $this->createForm('article', $draft);

        if ($request->getMethod() == 'POST')
        {
            $form->handleRequest($request);

            if($form->isValid() && $isWritingLocked === false)
            {
                $em = $this->getDoctrine()->getManager();
                $removedMetaKeys = $form['metaExtras']->getData() ? explode(':', $form['metaExtras']->getData()) : array();

                //force author if no permission to switch blog author
                if($article->getAuthor() != $draft->getAuthor())
                {
                    if($this->get('security.context')->isGranted('SWITCH_ARTICLE_AUTHOR'))
                    {
                        //switch author authorized
                        $article->setAuthor($draft->getAuthor());

                        $em->persist($article);
                    }
                    else
                    {
                        //switch back to original author
                        $draft->setAuthor( $article->getAuthor() );
                    }
                }

                if( $this->get('security.context')->isGranted('PUBLISH_ARTICLE', $article) && (($article->getStatus() == Article::STATUS_DRAFTED && $form->get('publish')->isClicked()) || $article->getStatus() == Article::STATUS_PUBLISHED && $form->get('update')->isClicked()) )
                {
                    if($this->get('security.context')->isGranted('EDIT_PUBLISH_STATUS', $draft) && $article->getStatus() != $draft->getStatus())
                    {
                        //article converted to draft
                        $article->setStatus($draft->getStatus());
                    }
                    else
                    {
                        //new changes should be published or updated for already published articles
                        $this->get('article_generator')->generateArticleFromDraft($article, $draft);

                        foreach($article->getMetaData() as $meta)
                        {
                            if( in_array($meta->getKey(), $removedMetaKeys))
                            {
                                $article->removeMetaData($meta);
                            }
                        }

                        $article
                            ->setPublishedAt(new \DateTime());
                    }

                    $em->persist($article);
                }

                //save latest draft as a new revision
                if($draft->getTitle() != $latestDraft->getTitle() || $draft->getContent() != $latestDraft->getContent())
                {
                    //save as new revision
                    $draft
                        ->setStatus(Article::STATUS_DRAFTED)
                        ->setSlug('drafted-' . $article->getId());

                    //copy title to latestDraft for updated display in article list
                    $parentDraft = $latestDraft->getParent();
                    $parentDraft
                        ->setTitle($draft->getTitle());

                    $em->persist($parentDraft);
                    $em->persist($draft);
                }
                else
                {
                    //update latest revision
                    //but skip metadata to detect removed
                    $this->get('article_generator')->loadArticle($latestDraft, $draft, true, false);

                    $latestDraft
                        ->setStatus(Article::STATUS_DRAFTED);

                    //removing deleted metaData manually
                    foreach($latestDraft->getMetaData() as $meta)
                    {
                        if( in_array($meta->getKey(), $removedMetaKeys))
                        {
                            $latestDraft->removeMetaData($meta);
                        }
                    }

                    $em->persist($latestDraft);
                }

                $em->flush();

                //note - must be after flush, depends on DB
                $dispacher->dispatch(EDBlogEvents::ED_BLOG_ARTICLE_POST_UPDATE, new ArticleAdministrationEvent($article));

                return $this->redirectToRoute('ed_blog_admin_article_edit', array('slug' => $article->getSlug()));
            }
        }

        return $this->render("EDBlogBundle:Article:create.html.twig",
            array(
                'form' => $form->createView(),
                'form_media' => $formMedia->createView(),
                'form_excerpt_media' => $formExcerptMedia->createView(),
                'medias' => $mediaManager->findAll(),
                'article' => $draft,
                'isLocked' => $isWritingLocked,
                'lockedBy' => ($isWritingLocked === false) ? null : $this->getDoctrine()->getRepository(get_class($user))->findOneBy(array('id' => $isWritingLocked))
            ));
    }

    /**
     * @Route("/article/autosave/{slug}", name="ed_blog_article_autosave")
     * @ParamConverter("article", class="ED\BlogBundle\Interfaces\Model\ArticleInterface", converter="abstract_converter")
     */
    public function autosaveAction(Request $request, $article)
    {
        $user = $this->getBlogUser();

        if(!$request->isXmlHttpRequest())
            return $this->redirectToRoute('ed_blog_homepage_index');

        if($this->get('security.context')->isGranted('EDIT_ARTICLE', $article) === false)
        {
            return new JsonResponse( array(
                "success" => false,
                "message" => "This content is currently unavailable. It may be temporarily unavailable, the link you clicked on may have expired, or you may not have permission to view this page."
            ));
        }

        //get latest drafted version of the article
        $latestDraft = $this->get('app_repository_article')->findOneBy(array(
            "status" => Article::STATUS_DRAFTED,
            "parent" => $article
        ), array(
            "modifiedAt" => "DESC",
            "id" => "DESC"
        ));

        $latestDraft = $latestDraft ? $latestDraft : $article;

        $title = $request->get('title', $latestDraft->getTitle());
        $content = $request->get('content', $latestDraft->getContent());
        $em  = $this->getDoctrine()->getManager();
        $autosaved = false;

        if($title != $latestDraft->getTitle() || $content != $latestDraft->getContent())
        {
            $latestDraft
                ->setTitle($title)
                ->setContent($content);

            if($latestDraft->getAuthor() != $user)
            {
                //save as new revision
                $draft = $this->get('article_generator')->generateNewDraftFromLatestRevision($latestDraft, $article);
                $draft
                    ->setStatus(Article::STATUS_DRAFTED)
                    ->setSlug('drafted-' . $article->getId())
                    ->setAuthor($user);

                $em->persist($draft);
            }
            else
            {
                //overwrite previous revision
                $em->persist($latestDraft);
            }

            $em->flush();
            $autosaved = true;
        }

        return new JsonResponse(array(
            'success' => true,
            'autosaved' => $autosaved
        ));
    }

    /**
     * @Route("/article/media/list", name="ed_blog_admin_article_media_list")
     */
    public function mediaListAction(Request $request)
    {
        $user = $this->getBlogUser();
        $paginator = $this->get('ed_blog.paginator');

        if($request->get('excerpt', false))
        {
            $response = $this->getPaginated($paginator, array(), 'Excerpt');
        }
        else
        {
            $response = $this->getPaginated($paginator);
        }

        return $response;
    }

    /**
     * @Route("/upload", name="ed_blog_admin_article_upload")
     */
    public function uploadAction(Request $request)
    {
        $user = $this->getBlogUser();
        $mediaManager = $this->container->get('sonata.media.manager.media');

        if(!$request->get('excerpt', false))
        {
            $form = $this->createForm(new ArticlePhotoType());
        }
        else
        {
            $form = $this->createForm(new ArticleExcerptType());
        }

        if ($request->getMethod() == 'POST')
        {
            $form->handleRequest($request);
            $dispatcher = $this->get('event_dispatcher');
            $attachment = $form['media']->getData();

            //to support single file upload (multiple=false)
            if(!is_array($attachment))
            {
                $attachment = array($attachment);
            }

            $mediaArray = new ArrayCollection();
            $errorArray = array();

            foreach($attachment as $attached)
            {
                if(in_array($attached->getMimeType(), array(
                    'image/pjpeg', 'image/jpeg', 'image/png', 'image/x-png', 'image/gif',
                )))
                {
                    try
                    {
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

            $dispatcher->dispatch(EDBlogEvents::ED_BLOG_MEDIA_LIBRARY_MEDIA_UPLOADED, new MediaArrayEvent($mediaArray));
        }

        $paginator = $this->get('ed_blog.paginator');
        $response = $this->getPaginated($paginator, array('errors' => $errorArray));

        return $response;
    }

    private function getPaginated($paginator, $ajaxParams = array(), $folder='Media')
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
            'EDBlogBundle:Article/' . $folder. ':list',
            'EDBlogBundle:Global:pagination',
            array(),
            Pagination::DOZEN,
            null,
            'EDBlogBundle:Global:pagination.html.twig',
            $ajaxParams,
            'ed_blog_admin_article_media_list'
        );

        return $response;
    }

    /**
     * @Route("/article/list", name="ed_blog_admin_article_list")
     */
    public function listAction(Request $request)
    {
        $orderBy = $request->get('orderby', null);
        $order = $request->get('order', null);

        $user = $this->getBlogUser();

        $paginator = $this->get('ed_blog.paginator');
        //$articles = $this->get('app_repository_article')->getArticlesList($orderBy, $order);
        $articles = $this->get('app_repository_article')->getSortableQuery($orderBy, $order);
        $response = $paginator->paginate(
            $articles,
            'EDBlogBundle:Article:list',
            'EDBlogBundle:Global:paginationClassic',
            array('orderBy'=>$orderBy, 'order'=>$order),
            Pagination::MEDIUM,
            null,
            'EDBlogBundle:Global:paginationClassic.html.twig',
            array()
        );

        return $response;
    }

    /**
     * @Route("/article/show/{slug}", name="ed_blog_admin_article_show")
     * @ParamConverter("article", class="ED\BlogBundle\Interfaces\Model\ArticleInterface", converter="abstract_converter")
     */
    public function showAction(Request $request, Article $article)
    {
        $user = $this->getBlogUser();
        $comment = $this->get('comment_generator')->getObject();
        $comment
            ->setArticle($article)
            ->setAuthor($user);

        $form = $this->createForm('edcomment', $comment);
        $comments =  $this->get("app_repository_comment")->findByArticle($article, $this->get("blog_settings")->getCommentsDisplayOrder());

        return $this->render("EDBlogBundle:Article:show.html.twig",
            array(
                'article' => $article,
                'form' => $form->createView(),
                'comments' => $comments
            ));
    }

    /**
     * @Route("/article/{slug}/remove", name="ed_blog_admin_article_delete")
     * @ParamConverter("article", class="ED\BlogBundle\Interfaces\Model\ArticleInterface", converter="abstract_converter")
     */
    public function deleteAction($article)
    {
        $user = $this->getBlogUser();

        if($this->get('security.context')->isGranted('EDIT_ARTICLE', $article) === false)
        {
            throw new AccessDeniedException('This content is currently unavailable. It may be temporarily unavailable, the link you clicked on may have expired, or you may not have permission to view this page.');
        }

        $em = $this->getDoctrine()->getManager();
        $taxonomies = array_merge( $article->getCategories()->toArray(), $article->getTags()->toArray() );

        $em->remove($article);
        $em->flush();

        $dispacher = $this->get('event_dispatcher');
        $dispacher->dispatch(EDBlogEvents::ED_BLOG_ARTICLE_REMOVED, new TaxonomyArrayEvent( $taxonomies ));

        return $this->redirectToRoute('ed_blog_admin_article_list');
    }

    /**
     * @Route("/article/{id}/check", name="ed_blog_article_check_writing_lock")
     * @ParamConverter("article", class="ED\BlogBundle\Interfaces\Model\ArticleInterface", converter="abstract_converter")
     */
    public function checkWritingLockAction(Request $request, $article)
    {
        $user = $this->getBlogUser();

        if(!$request->isXmlHttpRequest())
            return $this->redirectToRoute('ed_blog_homepage_index');

        $isWritingLocked = $this->get('ed_blog.handler.article_handler')->isLocked($user, $article);

        if($isWritingLocked === false)
        {
            return new JsonResponse(array(
                'success' => true,
                'lock' => false,
            ));
        }
        else
        {
            $userLocked = $this->get('app_repository_user')->findOneById( $isWritingLocked );

            return new JsonResponse(array(
                'success' => true,
                'lock' => true,
                'html' =>  $this->renderView('EDBlogBundle:Article:checkWritingLock.html.twig', array(
                    'user' => $userLocked
                ))
            ));
        }

    }

    /**
     * @Route("/article/{id}/takeover", name="ed_blog_article_takeover")
     * @ParamConverter("article", class="ED\BlogBundle\Interfaces\Model\ArticleInterface", converter="abstract_converter")
     */
    public function takeoverAction($article)
    {
        $user = $this->getBlogUser();

        $this->get('ed_blog.handler.article_handler')->takeoverLock($user, $article);

        return $this->redirectToRoute('ed_blog_admin_article_edit', array(
            'slug' => $article->getSlug()
        ));
    }
}