<?php

namespace ED\BlogBundle\Controller\Frontend;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SidebarController extends Controller
{
    public function blogListAction()
    {
          $categories=$this->getDoctrine()->getManager()->getRepository('AppBundle:Taxonomy')->getAllParentCategories();
          $archiveYearsMonths=$this->getDoctrine()->getManager()->getRepository('AppBundle:Article')->getYearsMonthsOfArticles();

          return $this->render('EDBlogBundle:Frontend/Blog:blog_sidebar.html.twig', array( 'categories' => $categories,'archive' => $archiveYearsMonths ));
    }
}
