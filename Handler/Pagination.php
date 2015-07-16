<?php
/**
 * Created by PhpStorm.
 * User: Milos Milojevic (milos.milojevic@etondigital.com)
 * Date: 1/19/15
 * Time: 9:91 PM
 */

namespace ED\BlogBundle\Handler;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class Pagination
{
    private $paginator;
    private $templating;

    const TINY = 2;
    const SMALL = 5;
    const MEDIUM = 10;
    const INTERMEDIUM = 11;
    const DOZEN = 12;
    const LARGE = 20;

    public function __construct($paginator, $templating)
    {
        $this->paginator = $paginator;
        $this->templating = $templating;
    }

    /*
     * $data type: array , data for pagination
     * 
     * $templateCut type: string, Shorted path for the pagination renderView, used for ajax and classsic response.
     *      Example $templateCut = 'EDWebBundle:Message:list', templates that will be used are:
     *      'EDWebBundle:Message:list.hmtl.twig' for none ajax response , and for ajax response 'EDWebBundle:Message:listAjax.html.twig'
     * 
     * $templateCutPagination type: string, Shorted path for twig that will be used to render pager part
     *      Example: $templateCutPagination = 'EDWebBundle:Global:pagination' it will render 'EDWebBundle:Global:paginationAjax.html.twig'
     * 
     * $parametersForTwig type: array, Here we are rendering view component, if you need to send more than classic parametar 'pagination', add it here.
     * 
     * $limit  type: integer
     * 
     * $params type: array, if you have some get parameters that should be added into pager urls.
     * 
     * $paginationTemplate type: string, this is path for $pagination->setTemplate($paginationTemplate)
     *      If you want to override knp_pagination pager template send it here.
     * 
     *Sometimes there will be needs to return only elements of the list for load more pagination, there is check if pager is grarter than 1 than you should use
     * new twig template to list only elements and append it on page via jquery. Twig will be: $templateCut."AjaxElements.html.twig";
     *
     * $ajaxParams Parameters that will be sent with ajax, with alias and value for return Json
     */
    public function paginate(
        $data,
        $templateCut,
        $templateCutPagination,
        $parametarsForTwig,
        $limit = 10,
        $params = array(),
        $paginationTemplate = 'AppBundle:Account:pagination.html.twig',
        $ajaxParams = array(),
        $customRoute = null
    )
    {
        $request = Request::createFromGlobals();
        $page = $request->query->get('page',1);
        $pagination = $this->paginator->paginate($data, $page ,$limit);

        if($params && count($params))
        {
            foreach($params as $key => $param)
            {
                $pagination->setParam($key, $param);
            }
        }

        if($customRoute)
        {
            $pagination->setUsedRoute($customRoute);
        }

        $pagination->setTemplate($paginationTemplate);
        $parametarsForTwig['pagination'] = $pagination;

        if($request->isXmlHttpRequest())
        {
            //If you is bigger than first page that is it should return only list part which will be appended
            if($page > 1)
            {
                $htmlTwig = $templateCut."AjaxElements.html.twig";
            }else
            {
                $htmlTwig = $templateCut."Ajax.html.twig";
            }

            $jsonArray = array(
                'html' => $this->templating->render($htmlTwig, $parametarsForTwig),
                'pagination' => $this->templating->render($templateCutPagination.'Ajax.html.twig',$parametarsForTwig)
            );

            $jsonArray = array_merge($jsonArray, $ajaxParams);

            return new JsonResponse($jsonArray);
        }
        else
        {
            return new Response($this->templating->render($templateCut.".html.twig",$parametarsForTwig));
        }
    }
} 