<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 3.6.15.
 * Time: 13.39
 */

namespace ED\BlogBundle\Listener;

use Doctrine\Bundle\DoctrineBundle\Registry;
use ED\BlogBundle\Event\ArticleAdministrationEvent;
use ED\BlogBundle\Event\TaxonomyArrayEvent;
use ED\BlogBundle\Events\EDBlogEvents;
use ED\BlogBundle\Interfaces\Repository\BlogTaxonomyRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class ArticleEventListener implements EventSubscriberInterface
{
    protected $doctrine;
    protected $session;
    protected $taxonomyRepository;

    function __construct(Registry $doctrine, Session $session, BlogTaxonomyRepositoryInterface $taxonomyRepository)
    {
        $this->doctrine = $doctrine;
        $this->session = $session;
        $this->taxonomyRepository = $taxonomyRepository;
    }


    public static function getSubscribedEvents()
    {
        return array(
            EDBlogEvents::ED_BLOG_ARTICLE_PREUPDATE_INIT => "initialize",
            EDBlogEvents::ED_BLOG_ARTICLE_POST_UPDATE => "postSave",
            EDBlogEvents::ED_BLOG_ARTICLE_REMOVED => "postRemoved",
            EDBlogEvents::ED_BLOG_ARTICLE_CREATED => "postCreated"
        );
    }

    /**
     * Should save categories before update/create/delete/publish/draft
     * @param ArticleAdministrationEvent $event
     */
    public function initialize(ArticleAdministrationEvent $event)
    {
        $article = $event->getArticle();

        if($article)
        {
            $taxonomies = array_merge( $article->getCategories()->toArray() , $article->getTags()->toArray() );

            $this->session->set('taxonimies_of_' . $article->getId(), serialize($taxonomies));
        }
    }

    public function postSave(ArticleAdministrationEvent $event)
    {
        $article = $event->getArticle();

        if($article)
        {
            $taxonomies = array_merge( $article->getCategories()->toArray() , $article->getTags()->toArray() );

            if( $this->session->get('taxonimies_of_' . $article->getId(), null) )
            {
                $oldTaxonomies = unserialize( $this->session->get('taxonimies_of_' . $article->getId() ) );

                $taxonomies = array_merge($taxonomies,  $oldTaxonomies);
            }

            $categories = $this->taxonomyRepository->getArticleCategoryCount($taxonomies);
            $tags = $this->taxonomyRepository->getArticleTagCount($taxonomies);

            $allTaxonomies = array_merge($categories, $tags);

            //empty taxonomies will be not present in array $allTaxonomies
            foreach($taxonomies as $tax)
            {
                $hasCount = false;

                foreach($allTaxonomies as $calculatedTax)
                {
                    if($calculatedTax[0]->getId() == $tax->getId())
                    {
                        $hasCount = true;
                        break;
                    }
                }

                if(!$hasCount)
                {
                    $allTaxonomies[] = array($tax, 0);
                }
            }

            $this->updateArticleCount( $allTaxonomies );

            $this->session->getFlashBag()->add('success', 'Contest updated successfully.');
        }
    }

    public function postRemoved(TaxonomyArrayEvent $event)
    {
        $this->update($event);

        $this->session->getFlashBag()->add('success', 'Contest removed successfully.');
    }

    public function postCreated(TaxonomyArrayEvent $event)
    {
        $this->update($event);

        $this->session->getFlashBag()->add('success', 'Contest created successfully.');
    }

    private function update(TaxonomyArrayEvent $event)
    {
        $taxonomies = $event->getTaxonomies();
        $categories = $this->taxonomyRepository->getArticleCategoryCount($taxonomies);
        $tags = $this->taxonomyRepository->getArticleTagCount($taxonomies);

        $this->updateArticleCount( array_merge($categories, $tags) );
    }

    private function updateArticleCount($taxonomies)
    {
        foreach($taxonomies as $row )
        {
            $taxonomy = $row[0];
            $count = $row[1];

            $this->taxonomyRepository->updateTaxonomyCount($taxonomy, $count);
//            $taxonomy->setCount($count);
//            $this->doctrine->getManager()->persist($taxonomy);
        }

//        $this->doctrine->getManager()->flush();
    }

}