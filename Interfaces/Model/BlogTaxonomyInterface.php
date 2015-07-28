<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 21.5.15.
 * Time: 11.23
 */

namespace ED\BlogBundle\Interfaces\Model;


interface BlogTaxonomyInterface
{
    public function getTerm();

    public function setTerm(BlogTermInterface $term);

    public function getType();

    public function setType($type);

    public function getParent();

    public function setParent(BlogTaxonomyInterface $parent=null);

    public function getCount();

    public function setCount($value);

    public function getDescription();

    public function setDescription($description);

    public function getChildren();

    public function setChildren($children);

    public function getArticles();

    public function setArticles($articles);

    public function getTagged();

    public function setTagged($tagged);
}