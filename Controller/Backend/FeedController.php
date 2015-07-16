<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 15.6.15.
 * Time: 14.44
 */

namespace ED\BlogBundle\Controller\Backend;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class FeedController extends Controller
{
    /**
     * Generate the article feed
     */
    public function feedAction($type)
    {
        $articles = $this->get('app_repository_article')->getActiveArticles();

        $feed = $this->get('eko_feed.feed.manager')->get('article');
        $feed->addFromArray($articles);

        return new Response($feed->render($type)); // or 'atom'
    }
}