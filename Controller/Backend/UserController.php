<?php

namespace ED\BlogBundle\Controller\Backend;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use ED\BlogBundle\Handler\Pagination;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class UserController extends DefaultController
{
    /**
     * @Route("/user/list", name="ed_blog_user_list")
     */
    public function listAction(Request $request)
    {
        $user = $this->getBlogAdministrator();
        $paginator = $this->get('ed_blog.paginator');
        $orderBy = $request->get('orderby', null);
        $order = $request->get('order', null);

        $response = $paginator->paginate(
            $this->get('app_repository_user')->getSortableQuery($orderBy, $order, $this->container->getParameter('article_class')),
            'EDBlogBundle:Users:list',
            'EDBlogBundle:Global:pagination',
            array(
                'orderBy' => $orderBy,
                'order' => $order
            ),
            Pagination::MEDIUM,
            null,
            'EDBlogBundle:Global:paginationClassic.html.twig',
            array()
        );

        return $response;
    }

    /**
     * @Route("/user/add", name="ed_blog_user_add")
     */
    public function addAction(Request $request)
    {
        $user = $this->getBlogAdministrator();

        $form = $this->createForm('edblog_user_import');

        if($request->isMethod('post'))
        {
            $form->handleRequest($request);

            if($form->isValid())
            {
                $em = $this->getDoctrine()->getManager();
                $blogUser = $form['user']->getData();

                $this->get('ed_blog.handler.blog_user_handler')->revokeBlogRoles($blogUser);
                $blogUser
                    ->addRole('ROLE_BLOG_USER')
                    ->addRole($form['adminRole']->getData());

                $em->persist($blogUser);
                $em->flush();
            }

            $this->get('session')->getFlashBag()->add('success', 'User imported successfully.');
        }

        return $this->render('@EDBlog/Users/add.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/user/search", name="ed_blog_user_search")
     */
    public function searchAction(Request $request)
    {
        $user = $this->getBlogAdministrator();

        $results = $this
            ->getDoctrine()
            ->getRepository( get_class($user))
            ->createQueryBuilder('u')
            ->select('u.email AS value')
            ->where('u.email LIKE :term')
            ->andWhere('u.enabled = 1')
            ->andWhere('u.roles NOT LIKE :roleBlogUser')
            ->setParameter('term', "%" . $request->get('query') . "%")
            ->setParameter('roleBlogUser', '%ROLE_BLOG_USER%')
            ->getQuery()
            ->getResult();

        return new JsonResponse(array( "suggestions" => $results));
    }

    /**
     * @Route("/user/edit/{username}", name="ed_blog_user_edit")
     * @ParamConverter("user", class="ED\BlogBundle\Interfaces\Model\BlogUserInterface", converter="abstract_converter")
     */
    public function editAction(Request $request, $user)
    {
        $admin = $this->getBlogAdministrator();

        $form = $this->createForm('edblog_user', array(
            'role' => $this->getDefaultBlogRole($user),
            'blogDisplayName' => $user->getBlogDisplayName()
        ));

        if($request->isMethod('post'))
        {
            $form->handleRequest($request);

            if($form->isValid())
            {
                $this->get('ed_blog.handler.blog_user_handler')->revokeBlogRoles($user);
                $user
                    ->setBlogDisplayName( $form['blogDisplayName']->getData() )
                    ->addRole('ROLE_BLOG_USER')
                    ->addRole( $form['role']->getData() );

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', 'User updated successfully.');
            }
        }

        return $this->render('@EDBlog/Users/edit.html.twig', array(
            'form' => $form->createView(),
            'user' => $user
        ));
    }

    /**
     * @Route("/user/{username}/revoke", name="ed_blog_user_revoke")
     * @ParamConverter("user", class="ED\BlogBundle\Interfaces\Model\BlogUserInterface", converter="abstract_converter")
     */
    public function revokeAction($user)
    {
        $admin = $this->getBlogAdministrator();
        $em = $this->getDoctrine()->getManager();

        $this->get('ed_blog.handler.blog_user_handler')->revokeBlogRoles($user);

        $em->persist($user);
        $em->flush();
        $this->get('session')->getFlashBag()->add('success', 'User revoked successfully.');

        return $this->redirect($this->generateUrl('ed_blog_user_list'));
    }

}


