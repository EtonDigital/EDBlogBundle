<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 25.5.15.
 * Time: 10.07
 */

namespace ED\BlogBundle\Interfaces\Repository;


interface ArticleRepositoryInterface
{
    public function getArticlesList();

    public function getSortableQuery();
}