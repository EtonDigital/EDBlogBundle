<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 14.5.15.
 * Time: 10.57
 */

namespace ED\BlogBundle\Interfaces\Model;


interface BlogUserInterface
{
    public function getBlogDisplayName();

    public function setBlogDisplayName($blogDisplayName);
}