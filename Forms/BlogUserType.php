<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 26.6.15.
 * Time: 15.26
 */

namespace ED\BlogBundle\Forms;


use ED\BlogBundle\Handler\BlogUserHandler;
use ED\BlogBundle\Transformers\UserToEmailTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class BlogUserType extends AbstractType
{
    protected $blogUserHandler;

    function __construct(BlogUserHandler $blogUserHandler)
    {
        $this->blogUserHandler = $blogUserHandler;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {

           $builder
               ->add('role', 'choice', array(
                'label' => 'Roles?',
                'expanded' => true,
                'choices' => $this->blogUserHandler->getBlogRolesArray(),
                'data' => isset($options['data']['role']) ? $options['data']['role'] : null
                ))
               ->add('Save', 'submit', array(
                'attr' => array(
                    'class' => 'btn btn-md btn-primary btn-wide--xl flright--responsive-mob margin--b'
                ))
            );
    }

    public function getName()
    {
        return "edblog_user";
    }
}