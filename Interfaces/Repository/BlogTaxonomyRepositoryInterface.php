<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 25.5.15.
 * Time: 10.53
 */

namespace ED\BlogBundle\Interfaces\Repository;

use ED\BlogBundle\Interfaces\Model\BlogTaxonomyInterface;

interface BlogTaxonomyRepositoryInterface
{
    /**
     * Finds Taxonomy by Term slug
     * @param $slug
     * @return mixed
     */
    public function findBySlug($slug);

    public function getAllCategories();

    public function getAllParentCategories();

    public function getArticleCategoryCount($categoryIds);

    public function getAllTags();

    public function getArticleTagCount($tagIds);

    public function updateTaxonomyCount(BlogTaxonomyInterface $taxonomy, $count);

    public function getSortableQuery($orderBy, $order);

    public function removeAll();

    public function getTagByTitles($tagTitles);

    public function getTopNTags($number);
}