<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 16.6.15.
 * Time: 10.48
 */

namespace ED\BlogBundle\Controller\Backend;

use ED\BlogBundle\Forms\SettingsType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SettingsController extends DefaultController
{
    /**
     * @Route("/settings/edit", name="ed_blog_admin_settings_edit")
     */
    public function editAction(Request $request)
    {
        $user = $this->getBlogAdministrator();

        $settingsClass = $this->container->getParameter('blog_settings_class');

        $data = $this->getDoctrine()->getRepository($settingsClass)->getSettingsArray();
        $form = $this->createForm(new SettingsType(), $data);

        if($request->isMethod('POST'))
        {
            $form->handleRequest($request);

            if($form->isValid())
            {
                $success = $this->get('blog_settings')->saveSettings($form->getData());

                if( $success !== true )
                {
                    $form->addError(new FormError($success));
                }
                else
                {
                    $this->get('session')->getFlashBag()->add('success', 'Settings updated successfully.');
                    $this->redirect("ed_blog_admin_settings_edit");
                }
            }
        }

        return $this->render('@EDBlog/Settings/edit.html.twig', array(
            'form' => $form->createView()
        ));

    }

    /**
     * @Route("/settings/showDateTime", name="ed_blog_admin_settings_show_format")
     */
    public function showFormat(Request $request)
    {
        $currentDateTime=new \DateTime();
        $format=$request->get('dataFormat');

        return new JsonResponse(array(
            'html' => $currentDateTime->format($format)
        ));
    }
}
