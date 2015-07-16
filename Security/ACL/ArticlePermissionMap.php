<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 2.7.15.
 * Time: 08.56
 */

namespace ED\BlogBundle\Security\ACL;


final class ArticlePermissionMap
{
    private $attributes = array(
        'EDIT_ARTICLE',
        'PUBLISH_ARTICLE',
        'EDIT_PUBLISH_STATUS'
    );

    public function supports($attribute)
    {
        return in_array($attribute, $this->attributes, true);
    }
}