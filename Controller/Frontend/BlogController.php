<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 12.5.15.
 * Time: 10.45
 */

namespace ED\BlogBundle\Controller\Frontend;

use ED\BlogBundle\Forms\CommentType;
use ED\BlogBundle\Handler\Pagination;
use ED\BlogBundle\Model\Entity\Article;
use ED\BlogBundle\Model\Entity\Taxonomy;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BlogController extends Controller
{
    /**
     * @Route("/", name="ed_blog_homepage")
     * @Route("/blog", name="ed_blog_frontend_index")
     * @Route("/blog/")
     */
    public function indexAction()
    {

        $paginator = $this->get('ed_blog.paginator');
        $response = $paginator->paginate(
            $this->get('app_repository_article')->getActiveArticles(),
            'EDBlogBundle:Frontend/Blog:index',
            'EDBlogBundle:Frontend/Global:pagination',
            array(),
            Pagination::SMALL,
            null,
            $paginationTemplate = 'EDBlogBundle:Frontend/Global:pagination.html.twig',
            array(),
            null
        );

        return $response;
    }

    /**
     * @Route("/blog/{slug}", name="ed_frontend_blog_single_article")
     * @ParamConverter("article", class="ED\BlogBundle\Interfaces\Model\ArticleInterface", converter="abstract_converter")
     */
    public function singleArticleAction($article)
    {
        if($article->getStatus() != Article::STATUS_PUBLISHED  || !$article->getPublishedAt() || strtotime($article->getPublishedAt()->format("Y-m-d H:i:s") ) > strtotime(date("Y-m-d H:i:s")))
        {
            throw new NotFoundHttpException("Sorry, requested article is not longer available or your URL is wrong!");
        }

        $commentClass = $this->container->getParameter('blog_comment_class');
        $newComment = new $commentClass();
        $newComment
            ->setAuthor($this->getUser())
            ->setArticle($article);

        $form = $this->createForm(CommentType::class, $newComment);
        $comments =  $this->get("app_repository_comment")->findByArticle($article, $this->get("blog_settings")->getCommentsDisplayOrder());
        $commentsCount = $this->get("app_repository_comment")->findCountByArticle($article);

        return $this->render("EDBlogBundle:Frontend/Blog:singleArticle.html.twig",
            array(
                'article' => $article,
                'form' => $form->createView(),
                'comments' => $comments,
                'commentsCnt' => $commentsCount
                ));
    }

    /**
     * @Route("/blog/category/{categorySlug}", name="ed_frontend_blog_by_category")
     */
    public function byCategoryAction($categorySlug)
    {
        $taxonomyType = Taxonomy::TYPE_CATEGORY;
        $taxonomy = $this->get('app_repository_taxonomy')->findBySlug($categorySlug);

        if(!($taxonomy && $taxonomy->getType()==$taxonomyType))
        {
            throw new NotFoundHttpException("Category not found.");
        }

        $criteria['type'] = $taxonomyType;
        $criteria['value'] = $taxonomy;

        $paginator = $this->get('ed_blog.paginator');
        $response = $paginator->paginate(
            $this->get('app_repository_article')->getActiveArticlesByTaxonomy($categorySlug,$taxonomyType),
            'EDBlogBundle:Frontend/Blog:index',
            'EDBlogBundle:Frontend/Global:pagination',
            array("criteria" => $criteria),
            Pagination::SMALL,
            null,
            $paginationTemplate = 'EDBlogBundle:Frontend/Global:pagination.html.twig',
            array(),
            null
        );

        return $response;
    }

    /**
     * @Route("/blog/tag/{tagSlug}", name="ed_frontend_blog_by_tag")
     */
    public function byTagAction($tagSlug)
    {
        $taxonomyType = Taxonomy::TYPE_TAG;

        $taxonomy = $this->get('app_repository_taxonomy')->findBySlug($tagSlug);

        if(!($taxonomy && $taxonomy->getType()==$taxonomyType))
        {
            throw new NotFoundHttpException("Tag not found.");
        }

        $criteria['type'] = $taxonomyType;
        $criteria['value'] = $taxonomy;

        $paginator = $this->get('ed_blog.paginator');
        $response = $paginator->paginate(
            $this->get('app_repository_article')->getActiveArticlesByTaxonomy($tagSlug,$taxonomyType),
            'EDBlogBundle:Frontend/Blog:index',
            'EDBlogBundle:Frontend/Global:pagination',
            array("criteria" => $criteria),
            Pagination::SMALL,
            null,
            $paginationTemplate = 'EDBlogBundle:Frontend/Global:pagination.html.twig',
            array(),
            null
        );

        return $response;
    }

    /**
     * @Route("/blog/author/{username}", name="ed_frontend_blog_by_author")
     * @ParamConverter("user", class="ED\BlogBundle\Interfaces\Model\BlogUserInterface", converter="abstract_converter")
     */
    public function byAuthorAction($user)
    {
        $criteria['type'] = "author";
        $criteria['value'] = $user;

        $paginator = $this->get('ed_blog.paginator');
        $response = $paginator->paginate(
            $this->get('app_repository_article')->getActiveArticlesByAuthor($user),
            'EDBlogBundle:Frontend/Blog:index',
            'EDBlogBundle:Frontend/Global:pagination',
            array("criteria" => $criteria),
            Pagination::SMALL,
            null,
            $paginationTemplate = 'EDBlogBundle:Frontend/Global:pagination.html.twig',
            array(),
            null
        );

        return $response;
    }

    /**
     * @Route("/blog/archive/{yearMonth}", name="ed_frontend_blog_archive")
     */
    public function archiveAction($yearMonth)
    {
        $archive=explode('-',$yearMonth);
        $year=$archive[0];
        $month=(count($archive) > 1) ? $archive[1]: null ;

        if(!((int)$year == $year && (int)$year > 0 && $month && (int)$month == $month && (int)$month > 0 && (int)$month <= 12))
        {
            throw new NotFoundHttpException("Invalid archive period.");
        }

        $criteria['type'] = "archive";
        $criteria['value'] = array('year' => $year, 'month' => $month);

        $paginator = $this->get('ed_blog.paginator');
        $response = $paginator->paginate(
            $this->get('app_repository_article')->getArticlesInOneMonth($year,$month),
            'EDBlogBundle:Frontend/Blog:index',
            'EDBlogBundle:Frontend/Global:pagination',
            array("criteria" => $criteria),
            Pagination::SMALL,
            null,
            $paginationTemplate = 'EDBlogBundle:Frontend/Global:pagination.html.twig',
            array(),
            null
        );

        return $response;
    }



}


