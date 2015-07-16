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

class ImportUserType extends AbstractType
{
    private $userClass;
    private $entityManager;
    private $blogUserHandler;

    function __construct($userClass, $entityManager, BlogUserHandler $blogUserHandler)
    {
        $this->userClass = $userClass;
        $this->entityManager = $entityManager;
        $this->blogUserHandler = $blogUserHandler;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $userTransformer = new UserToEmailTransformer( $this->entityManager->getManager(), $this->userClass );

        $builder
            ->add(
                $builder->create('user','text', array(
                    'required' => true,
                    'attr' => array(
                        "class" => "form-control form-control--lg margin--halfb",
                        "placeholder" => "Search",
                        "data-ed-autocomplete" => true
                    )))->addModelTransformer($userTransformer)
            )
            ->add('adminRole', 'choice', array(
                'label' => 'Roles?',
                'expanded' => true,
                'choices' => $this->blogUserHandler->getBlogRolesArray()
            ))
            ->add('Save', 'submit', array(
                'attr' => array(
                    'class' => 'btn btn-md btn-primary btn-wide--xl flright--responsive-mob margin--b'
                ))
            );
    }

    public function getName()
    {
        return "edblog_user_import";
    }
}