<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 21.5.15.
 * Time: 10.44
 */

namespace ED\BlogBundle\Interfaces\Model;


interface BlogTermInterface
{
    public function getTitle();

    public function setTitle($title);

    public function getSlug();

    public function setSlug($slug);
}