<?php
/**
 * Created by Eton Digital.
 * User: Milos Milojevic (milos.milojevic@etondigital.com)
 * Date: 6/2/15
 * Time: 1:53 PM
 */

namespace ED\BlogBundle\Controller\Backend;

use ED\BlogBundle\Event\CommentEvent;
use ED\BlogBundle\Events\EDBlogEvents;
use ED\BlogBundle\Handler\Pagination;
use ED\BlogBundle\Model\Entity\Comment;
use ED\BlogBundle\Util\IDEncrypt;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CommentController extends DefaultController
{
    /**
     * @Route("/comment/{slug}/create", name="ed_blog_admin_comment_create")
     * @ParamConverter("article", class="ED\BlogBundle\Interfaces\Model\ArticleInterface", converter="abstract_converter")
     */
    public function createAction(Request $request, $article)
    {
        $user = $this->getUser();
        $blogSettings = $this->get('blog_settings');

        if($this->container->get('security.authorization_checker')->isGranted('ACCESS_COMMENTS', $user) === false || $this->container->get('security.authorization_checker')->isGranted('CREATE_COMMENT', $user) === false)
            throw new AccessDeniedHttpException("Sorry, commenting is currently disabled by blog administrator.");

        $object = $this->get('comment_generator')->getObject();
        $object
            ->setAuthor($user)
            ->setArticle($article);
        $class = get_class($object);

        $form = $this->createForm('edcomment', $object);

        if($request->isMethod('POST'))
        {
            $form->handleRequest($request);

            if($form->isValid())
            {
                $em = $this->getDoctrine()->getManager();

                if($user && $user->hasRole('ROLE_BLOG_ADMIN'))
                {
                    $object->setStatus( Comment::STATUS_ACTIVE );
                }
                else
                {
                    $object->setStatus( $blogSettings->manualCommentsApprove() ? Comment::STATUS_PENDING : Comment::STATUS_ACTIVE );
                }

                $em->persist($object);
                $em->flush();

                $dispatcher = $this->get("event_dispatcher");
                $dispatcher->dispatch(EDBlogEvents::ED_BLOG_COMMENT_CREATED, new CommentEvent($object));

                $resetObject = new $class;
                $resetObject
                    ->setAuthor($user)
                    ->setArticle($article);

                $form = $this->createForm('edcomment', $resetObject);
            }
        }

        $comments = $this->getDoctrine()->getRepository( $class )->findByArticle($article, $this->get("blog_settings")->getCommentsDisplayOrder());

        return new JsonResponse(array(
            'success' => true,
            'lock' => true,
            'currentComment'=>IDEncrypt::encrypt($object->getId()),
            'html' =>  $this->renderView("@EDBlog/Comment/list.html.twig", array(
                'form' => $form->createView(),
                'article' => $article,
                'comments' => $comments
            ))
        ));
    }

    /**
     * @Route("/comment/list", name="ed_blog_admin_comment_list")
     */
    public function listAction(Request $request)
    {
        $user = $this->getBlogUser();
        $this->checkCommentsAdministrator();

        $paginator = $this->get('ed_blog.paginator');
        $datetimeFormat = $this->get("blog_settings")->getDatetimeFormat();
        $orderBy = $request->get('orderby', null);
        $order = $request->get('order', null);

        $response = $this->getPaginated($paginator, null,  array(
            'datetimeFormat' => $datetimeFormat,
            'orderBy' => $orderBy,
            'order' => $order
        ));

        return $response;
    }

    private function getPaginated($paginator, $ajaxParams = array(), $templateParams = array())
    {
        $queryBuilder = $this->get('app_repository_comment')->getSortableQuery($templateParams['orderBy'], $templateParams['order']);

        $response = $paginator->paginate(
            $queryBuilder,
            'EDBlogBundle:Comment/Comments:list',
            'EDBlogBundle:Comment/Comments:paginationComments',
            $templateParams,
            Pagination::DOZEN,
            null,
            'EDBlogBundle:Comment/Comments:paginationComments.html.twig',
            $ajaxParams
        );

        return $response;
    }
    /**
     * @Route("/comment/{id}/remove", name="ed_blog_comment_delete")
     * @ParamConverter("comment", class="ED\BlogBundle\Interfaces\Model\CommentInterface", converter="abstract_converter")
     */
    public function deleteAction($comment)
    {
        $user = $this->getBlogUser();

        $em = $this->getDoctrine()->getManager();
        $em->remove($comment);
        $em->flush();

        $this->get('session')->getFlashBag()->add('success', 'Comment removed successfully.');
        return $this->redirectToRoute('ed_blog_admin_comment_list');
    }

    /**
     * @Route("/comment/{id}/status/{status}", name="ed_blog_comment_edit_status", requirements={"status": "0|1"})
     * @ParamConverter("comment", class="ED\BlogBundle\Interfaces\Model\CommentInterface", converter="abstract_converter")
     */
    public function editStatusAction(Request $request, $comment, $status)
    {
        $user = $this->getBlogUser();
        $this->checkCommentsAdministrator();

        $comment->setStatus($status);
        $em = $this->getDoctrine()->getManager();

        $em->persist($comment);
        $em->flush();

        if($request->isXmlHttpRequest())
        {
            return new JsonResponse(array(
                "success" => true,
                "html" => $this->renderView("@EDBlog/Comment/Comments/listAjaxElement.html.twig", array( "comment" => $comment))
            ));
        }
        else
        {
            $this->get('session')->getFlashBag()->add('success', 'Comment status updated successfully.');

            return $this->redirectToRoute("ed_blog_admin_comment_list");
        }

    }

    /**
     * @Route("/comment/{id}/edit", name="ed_blog_comment_edit")
     * @ParamConverter("comment", class="ED\BlogBundle\Interfaces\Model\CommentInterface", converter="abstract_converter")
     */
    public function editAction(Request $request, $comment)
    {
        $user = $this->getUser();
        $this->checkCommentsAdministrator();

        $form = $this->createForm('ed_blog_comment', $comment);

        if($request->isMethod("POST"))
        {
            $form->handleRequest($request);

            if($form->isValid())
            {
                $comment = $form->getData();
                $em = $this->getDoctrine()->getManager();

                $em->persist($comment);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', 'Comment updated successfully.');
                return $this->redirectToRoute('ed_blog_admin_comment_list');
            }
        }

        return $this->render('@EDBlog/Comment/edit.html.twig', array(
            'form' => $form->createView(),
            'comment' => $comment
        ));
    }
}