<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 3.7.15.
 * Time: 09.29
 */

namespace ED\BlogBundle\Handler;

use ED\BlogBundle\Interfaces\Model\BlogUserInterface;

class BlogUserHandler
{
    public function getBlogRolesArray()
    {
        return array(
            'Administrator' => 'ROLE_BLOG_ADMIN',
            'Editor' => 'ROLE_BLOG_EDITOR',
            'Author' => 'ROLE_BLOG_AUTHOR',
            'Contributor' => 'ROLE_BLOG_CONTRIBUTOR'
        );
    }

    public function getDefaultBlogRole( BlogUserInterface $user)
    {
        $role = "ROLE_BLOG_CONTRIBUTOR";

        if($user->hasRole('ROLE_BLOG_ADMIN'))
        {
            return 'ROLE_BLOG_ADMIN';
        }
        elseif($user->hasRole('ROLE_BLOG_EDITOR'))
        {
            return 'ROLE_BLOG_EDITOR';
        }
        elseif($user->hasRole('ROLE_BLOG_AUTHOR'))
        {
            return 'ROLE_BLOG_AUTHOR';
        }

        return $role;
    }


    public function getDefaultBlogRoleName( BlogUserInterface $user)
    {
        $role = $this->getDefaultBlogRole($user);
        $roleArray = explode('_', $role);
        $role = strtolower($roleArray[ count($roleArray) -1 ]);

        return ucfirst($role);
    }

    /**
     * Revokes all edBlog administration roles
     *
     * @param $user
     * @return mixed
     */
    public function revokeBlogRoles(&$user)
    {
        $user
            ->removeRole('ROLE_BLOG_USER')
            ->removeRole('ROLE_BLOG_ADMIN')
            ->removeRole('ROLE_BLOG_EDITOR')
            ->removeRole('ROLE_BLOG_AUTHOR')
            ->removeRole('ROLE_BLOG_CONTRIBUTOR');

        return $user;
    }
}