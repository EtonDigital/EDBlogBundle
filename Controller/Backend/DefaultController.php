<?php

namespace ED\BlogBundle\Controller\Backend;

use ED\BlogBundle\Interfaces\Model\BlogUserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

class DefaultController extends Controller
{
    public function getBlogUser()
    {
        $user = $this->getLoggedUser();

        if($this->container->get('security.authorization_checker')->isGranted('ROLE_BLOG_USER') === false)
            throw new AccessDeniedException('This content is currently unavailable. It may be temporarily unavailable, the link you clicked on may have expired, or you may not have permission to view this page.');

        return $user;
    }

    public function getBlogAdministrator()
    {
        $user = $this->getLoggedUser();

        if($this->container->get('security.authorization_checker')->isGranted('ROLE_BLOG_ADMIN') === false)
            throw new AccessDeniedException('This content is currently unavailable. It may be temporarily unavailable, the link you clicked on may have expired, or you may not have permission to view this page.');

        return $user;
    }

    private function getLoggedUser()
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('Please login first.');
        }

        return $user;
    }

    public function getDefaultBlogRole( BlogUserInterface $user=null)
    {
        if(!$user)
            $user = $this->getUser();

        return $this->get('ed_blog.handler.blog_user_handler')->getDefaultBlogRole($user);
    }
}
