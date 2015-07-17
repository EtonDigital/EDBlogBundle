<?php

namespace ED\BlogBundle\Controller\Frontend;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SidebarController extends Controller
{
    public function blogListAction()
    {
          $categories = $this->get('app_repository_taxonomy')->getAllParentCategories();
          $archiveYearsMonths = $this->get('app_repository_article')->getYearsMonthsOfArticles();

          return $this->render('EDBlogBundle:Frontend/Blog:blog_sidebar.html.twig', array(
              'categories' => $categories,
              'archive' => $archiveYearsMonths
          ));
    }
}
