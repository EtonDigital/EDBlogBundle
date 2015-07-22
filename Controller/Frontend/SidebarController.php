<?php

namespace ED\BlogBundle\Controller\Frontend;

use ED\BlogBundle\Model\Entity\Taxonomy;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SidebarController extends Controller
{
    public function blogListAction()
    {
          $categories = $this->get('app_repository_taxonomy')
              ->createQueryBuilder('c')
              ->where('c.parent IS NULL')
              ->andWhere('c.count > 0')
              ->andWhere('c.type = :categoryType')
              ->setParameter('categoryType', Taxonomy::TYPE_CATEGORY)
              ->getQuery()
              ->getResult();

          $archiveYearsMonths = $this->get('app_repository_article')->getYearsMonthsOfArticles();

          return $this->render('EDBlogBundle:Frontend/Blog:blog_sidebar.html.twig', array(
              'categories' => $categories,
              'archive' => $archiveYearsMonths
          ));
    }
}
