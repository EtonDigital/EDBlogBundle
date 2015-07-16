<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 22.5.15.
 * Time: 10.25
 */

namespace ED\BlogBundle\Interfaces\Model;


interface ArticleInterface
{
    public function setCategories($categories);

    public function getCategories();

    public function setTitle($title);

    public function getTitle();

    public function setContent($content);

    public function getContent();

    public function setExcerpt($excerpt);

    public function getExcerpt();

    public function setSlug($slug);

    public function getSlug();

    public function setStatus($status);

    public function getStatus();

    public function setPublishedAt($publishedAt);

    public function getPublishedAt();

    public function getAuthor();

    public function getMetaData();
}