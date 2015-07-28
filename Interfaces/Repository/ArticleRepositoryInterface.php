<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 25.5.15.
 * Time: 10.07
 */

namespace ED\BlogBundle\Interfaces\Repository;


use ED\BlogBundle\Interfaces\Model\BlogUserInterface;

interface ArticleRepositoryInterface
{
    /**
     * The list of published articles
     *
     * @return mixed
     */
    public function getArticlesList();

    public function getSortableQuery();

    public function getNumberOfActiveBlogs(BlogUserInterface $user);

    public function getActiveArticles($limit = null);

    public function getActiveArticlesByTaxonomy($taxonomySlug, $type, $limit = null);

    public function getActiveArticlesByAuthor(BlogUserInterface $author, $limit = null);

    public function removeAll();

    public function getArticlesInOneMonth($year, $month, $limit = null);

    public function getYearsMonthsOfArticles();


}