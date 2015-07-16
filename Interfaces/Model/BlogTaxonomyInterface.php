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

    public function setTerm($term);

    public function getType();

    public function setType($type);

    public function getParent();

    public function setParent(BlogTaxonomyInterface $parent=null);

    public function getCount();

    public function setCount($value);
}