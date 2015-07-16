<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 3.6.15.
 * Time: 13.30
 */

namespace ED\BlogBundle\Event;


use ED\BlogBundle\Interfaces\Model\ArticleInterface;
use Symfony\Component\EventDispatcher\Event;

class ArticleAdministrationEvent extends Event
{
    protected $article;

    function __construct(ArticleInterface $article=null)
    {
        $this->article = $article;
    }

    public function getArticle()
    {
        return $this->article;
    }
}