<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 17.6.15.
 * Time: 10.58
 */

namespace ED\BlogBundle\Controller\Frontend;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

class RedirectController extends Controller
{

    /**
     * This action will ask for authorization and redirect user back to referer
     *
     * @Route("/authenticate", name="ed_blog_redirect_authenticate")
     */
    public function authenticateAction(Request $request)
    {
        if(!$this->getUser())
        {
            //Get redirecting route
            $referer = $request->headers->get('referer');
            //Get section on the page to be redirected at
            $pageSection = $request->get('section', false);

            $this->get('session')->set('refererUrl', $referer);

            if($pageSection)
            {
                $this->get('session')->set('refererUrlSection', $pageSection);
            }
        }

        $user = $this->get('security.token_storage')->getToken()->getUser();
        if (!is_object($user) || !$user instanceof UserInterface)
        {
            throw new AccessDeniedException('Please login first.');
        }

        $redirectURL = $this->get('session')->get('refererUrl', null);
        $redirectURLSection = $this->get('session')->get('refererUrlSection', null);

        if(!$redirectURL || $redirectURL == $this->generateUrl('ed_blog_redirect_authenticate', array(), true))
        {
            return $this->redirectToRoute("ed_blog_homepage_index");
        }
        else
        {
            if($redirectURLSection)
            {
                $redirectURL .= '#' . $redirectURLSection;
                $this->get('session')->remove('refererUrlSection');
            }

            $this->get('session')->remove('refererUrl');

            return $this->redirect($redirectURL) ;
        }

    }

    /**
     * @Route("/login", name="ed_blog_redirect_login", condition="request.isXmlHttpRequest()")
     */
    public function ajaxLoginAction()
    {
        return new JsonResponse( array(
            "success" => false,
            "redirect" => $this->generateUrl("fos_user_security_login", array(), true)
        ));
    }
}