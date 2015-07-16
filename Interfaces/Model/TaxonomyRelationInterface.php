<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 22.5.15.
 * Time: 10.18
 */

namespace ED\BlogBundle\Interfaces\Model;


interface TaxonomyRelationInterface
{
    public function setArticle($article);

    public function getArticle();

    public function setTaxonomy($taxonomy);

    public function getTaxonomy();
}