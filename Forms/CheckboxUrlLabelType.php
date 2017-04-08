<?php

namespace ED\BlogBundle\Forms;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 * Form type for rendering a checkbox with a label that can contain links to
 * pages.
 *
 * Usage: supply an array with routes information with the form type options.
 * The form type will generate the urls using the router and replace the array
 * keys from the routes array with the urls in the form types label.
 *
 * A typical use case is a checkbox the user needs to check to accept terms
 * that are on a different page that has a dynamic route.
 *
 * @author Uwe Jäger <uwej711@googlemail.com>
 */
class CheckboxUrlLabelType extends AbstractType
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritDoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $routes = $options['routes'];
        $paths = array();
        foreach ($routes as $key => $route) {
            $name = isset($route['name']) ? $route['name'] : null;
            $parameters = isset($route['parameters']) ? $route['parameters'] : array();
            $referenceType = isset($route['referenceType']) ? $route['referenceType'] : UrlGeneratorInterface::ABSOLUTE_PATH;
            $paths[$key] = $this->router->generate($name, $parameters, $referenceType);
        }
        $view->vars['paths'] = $paths;
        parent::buildView($view, $form, $options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'routes' => array(),
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return CheckboxType::class;
    }

}
