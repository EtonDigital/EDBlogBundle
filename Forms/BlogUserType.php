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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
               ->add('blogDisplayName', TextType::class, array(
                   'label' => 'Display Name:',
                   'data' => isset($options['data']['blogDisplayName']) ? $options['data']['blogDisplayName'] : null,
                   'attr' => array(
                       'class' => 'form-control form-control--lg margin--b',
                       'placeholder' => 'Enter blog user name'
                   )
               ))
               ->add('role', ChoiceType::class, array(
                'label' => 'Roles?',
                'expanded' => true,
                'choices' => $this->blogUserHandler->getBlogRolesArray(),
                'data' => isset($options['data']['role']) ? $options['data']['role'] : null
                ))
               ->add('Save', SubmitType::class, array(
                'attr' => array(
                    'class' => 'btn btn-md btn-primary btn-wide--xl flright--responsive-mob margin--b'
                ))
            );
    }

    public function getBlockPrefix()
    {
        return "edblog_user";
    }
}