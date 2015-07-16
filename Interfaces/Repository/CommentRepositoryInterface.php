<?php
/**
 * Created by Eton Digital.
 * User: Milos Milojevic (milos.milojevic@etondigital.com)
 * Date: 6/2/15
 * Time: 3:24 PM
 */

namespace ED\BlogBundle\Interfaces\Repository;


use ED\BlogBundle\Interfaces\Model\ArticleInterface;

interface CommentRepositoryInterface
{
    /**
     * Get approved comments per article
     *
     * @param ArticleInterface $article
     * @param $order
     * @return mixed
     */
    public function findByArticle(ArticleInterface $article, $order);

    /**
     * Get number of approved comments per article
     *
     * @param ArticleInterface $article
     * @return mixed
     */
    public function findCountByArticle(ArticleInterface $article);

    /**
     * Will be used to display comments on Blog Administration list page
     *
     * @param $orderBy
     * @param $order
     * @return array
     */
    public function  getSortableQuery($orderBy, $order);
}