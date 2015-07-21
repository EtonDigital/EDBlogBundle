<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 7.5.15.
 * Time: 15.28
 */

namespace ED\BlogBundle\Security\ACL;

final class PermissionMap
{
    const ADMINISTRATE_BLOG = 'ADMINISTRATE_BLOG';

    private $attributes = array(
        'ADMINISTRATE_BLOG',
        'ACCESS_COMMENTS',
        'ACCESS_COMMENTS_LIST',
        'CREATE_COMMENT',
        'ADMINISTRATE_COMMENTS',
        'SWITCH_ARTICLE_AUTHOR'
    );

    public function supports($attribute)
    {
        return in_array($attribute, $this->attributes, true);
    }
}