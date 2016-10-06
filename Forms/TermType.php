<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 25.5.15.
 * Time: 14.48
 */

namespace ED\BlogBundle\Forms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class TermType extends AbstractType
{
    protected  $dataClass;

    function __construct($dataClass)
    {
        $this->dataClass = $dataClass;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', 'text', array(
                'required' => true,
                'label' => 'Title:',
                'attr' => array(
                    'class' => 'form-control form-control--lg margin--b',
                    'placeholder' => 'Enter title'
                ),
                'constraints' => array(
                    new NotBlank(array(
                        "message" => "Please enter title"
                    ))
                )
            ))
            ->add('slug', 'text', array(
                'required' => false,
                'label' => 'Slug:',
                'attr' => array(
                    'class' => 'form-control form-control--lg margin--b',
                    'placeholder' => 'Slug will be generated automatically from title or you can add it manually'
                )
            ))
           ;
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return "edterm";
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->dataClass,
        ));
    }


}