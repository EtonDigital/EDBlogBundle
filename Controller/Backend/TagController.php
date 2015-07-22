<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 4.6.15.
 * Time: 15.44
 */

namespace ED\BlogBundle\Controller\Backend;

use ED\BlogBundle\Handler\Pagination;
use ED\BlogBundle\Model\Entity\Taxonomy;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TagController extends DefaultController
{
    /**
     * @Route("/tag/express-create", name="ed_blog_tag_express_create")
     */
    public function createExpressAction(Request $request)
    {
        $this->getBlogUser();

        if($request->isXmlHttpRequest())
        {
            $title = $request->get('title', null);

            if(!$title)
            {
                return new JsonResponse(array(
                    'success' => false,
                    'message' => "Term title should be defined"
                ));
            }

            $existingTag = $this->get('app_repository_taxonomy')->getTagByTitles(array($title));

            if(!$existingTag)
            {
                $em = $this->getDoctrine()->getEntityManager();
                $term = $this->get('term_generator')->getObject();
                $term->setTitle($title);

                $taxonomy = $this->get('taxonomy_generator')->getObject();
                $taxonomy
                    ->setTerm($term)
                    ->setType(Taxonomy::TYPE_TAG);

                $em->persist($term);
                $em->persist($taxonomy);

                try
                {
                    $em->flush();

                    return new JsonResponse(array(
                        "success" => true
                    ));
                }
                catch (\Exception $e)
                {
                    return new JsonResponse(array(
                        "success" => false,
                        "message" => $e->getMessage()
                    ));
                }
            }

            return new JsonResponse(array(
                "success" => true
            ));
        }
        else
        {
            return $this->redirectToRoute('ed_blog_homepage_index');
        }
    }

    /**
     * @Route("/tag/list", name="ed_blog_tag_list")
     */
    public function listAction(Request $request)
    {
        $user = $this->getBlogAdministrator();
        $orderBy = $request->get('orderby', null);
        $order = $request->get('order', null);

        $paginator = $this->get('ed_blog.paginator');
        $response = $paginator->paginate(
            $this->get('app_repository_taxonomy')->getSortableQuery($orderBy, $order, Taxonomy::TYPE_TAG),
            'EDBlogBundle:Taxonomy/Tag:list',
            'EDBlogBundle:Taxonomy:pagination',
            array(
                'orderBy' => $orderBy,
                'order' => $order
            ),
            Pagination::DOZEN,
            null,
            $paginationTemplate = 'EDBlogBundle:Taxonomy:pagination.html.twig',
            array(),
            null
        );

        return $response;
    }

    /**
     * @Route("/tag/create", name="ed_blog_tag_create")
     */
    public function createAction(Request $request)
    {
        $user = $this->getBlogAdministrator();

        $form = $this->createForm('edtag', $this->get('taxonomy_generator')->getObject());

        if($request->isMethod('post'))
        {
            $form->handleRequest($request);
            $tag = $form->getData();

            if($tag->getTerm()->getTitle())
            {
                $existingTag = $this->get('app_repository_taxonomy')->getTagByTitles( array( $tag->getTerm()->getTitle() ) );

                if(!empty($existingTag))
                {
                    $existingTag = isset($existingTag[0]) ? $existingTag[0] : $existingTag;
                    $form->addError( new FormError("Sorry, similar tag \"" . $existingTag->getTerm()->getTitle()  . "\" already exists.") );
                }
            }

            if($form->isValid())
            {
                $em = $this->getDoctrine()->getManager();

                $em->persist($tag);
                $em->flush();

                return $this->redirectToRoute('ed_blog_tag_list');
            }
        }

        return $this->render("@EDBlog/Taxonomy/Tag/create.html.twig", array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/tag/{slug}/edit", name="ed_blog_tag_edit")
     */
    public function editAction(Request $request, $slug)
    {
        $user = $this->getBlogAdministrator();
        $taxonomy = $this->get('app_repository_taxonomy')->findBySlug($slug);
        if(!$taxonomy)
            throw new NotFoundHttpException("Sorry, requested resource can't be found.");

        $form = $this->createForm('edtag', $taxonomy);

        if($request->isMethod('post'))
        {
            $form->handleRequest($request);
            $tag = $form->getData();

            if($tag->getTerm()->getTitle())
            {
                $existingTags = $this->get('app_repository_taxonomy')->getTagByTitles( array( $tag->getTerm()->getTitle() ) );

                if(!empty($existingTags))
                {
                    //Disable editing Taxonomy title to matching Taxonomy title
                    foreach($existingTags as $existing)
                    {
                        if($existing->getId() != $tag->getId())
                        {
                            $form->addError( new FormError("Sorry, similar tag \"" . $existing->getTerm()->getTitle()  . "\" already exists.") );
                        }
                    }
                }
            }

            if($form->isValid())
            {
                $em = $this->getDoctrine()->getManager();

                $em->persist($form->getData());
                $em->flush();

                return $this->redirectToRoute('ed_blog_tag_list');
            }
        }

        return $this->render("@EDBlog/Taxonomy/Tag/edit.html.twig", array(
            'form' => $form->createView(),
            'slug' => $slug
        ));
    }

    /**
     * @Route("/tag/{slug}/remove", name="ed_blog_tag_delete")
     */
    public function deleteAction($slug)
    {
        $user = $this->getBlogAdministrator();
        $taxonomy = $this->get('app_repository_taxonomy')->findBySlug($slug);
        if(!$taxonomy)
            throw new NotFoundHttpException("Sorry, requested resource can't be found.");

        $em = $this->getDoctrine()->getManager();

        $em->remove($taxonomy);
        $em->flush();

        return $this->redirectToRoute('ed_blog_tag_list');
    }

    /**
     * @Route("/tag/toptags/{number}", name="ed_blog_tags_top_tags")
     */
    public function topTagsAction($number)
    {
          $topNtags = $this->get('app_repository_taxonomy')->getTopNTags($number);

          return $this->render("EDBlogBundle:Frontend/Blog:blog_sidebar_tags.html.twig", array('topNtags' => $topNtags));
    }
}