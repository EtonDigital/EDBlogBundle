<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 9.7.15.
 * Time: 09.12
 */

namespace ED\BlogBundle\Forms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ArticleMetaType extends AbstractType
{
    protected $dataClass;

    function __construct($dataClass)
    {
        $this->dataClass = $dataClass;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('key', 'text', array(
                'label' => 'Meta name:',
                'required' => true,
                'attr' => array(
                    'class' => 'form-control form-control--lg margin--halfb',
                    'placeholder' => 'Enter name of the meta tag'
                )
            ))
            ->add('value', 'textarea', array(
                'label' => 'Meta value:',
                'required' => true,
                'attr' => array(
                    'class' => 'form-control form-control--lg margin--halfb',
                    'rows' => 2,
                    'placeholder' => 'Enter value of the meta tag'
                )
            ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return "article_meta";
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->dataClass,
        ));
    }

}