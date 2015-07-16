<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 7.7.15.
 * Time: 14.42
 */

namespace ED\BlogBundle\Interfaces\Model;


interface ArticleMetaInterface
{
    public function getArticle();

    public function getKey();

    public function getValue();
}