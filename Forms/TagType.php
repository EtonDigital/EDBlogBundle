<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 5.6.15.
 * Time: 11.10
 */

namespace ED\BlogBundle\Forms;

use ED\BlogBundle\Forms\TermType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use ED\BlogBundle\Model\Entity\Taxonomy;
use Symfony\Component\Form\FormBuilderInterface;

class TagType extends TaxonomyType
{
    function __construct($dataClass)
    {
        parent::__construct($dataClass);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('term', TermType::class, array(
                'required' => true,
                'label' => 'Title:',
                'attr' => array(
                    'class' => 'form-control form-control--lg margin--b',
                    'placeholder' => 'Enter tag title'
                )
            ))
            ->add('description', TextType::class, array(
                'required' => false,
                'label' => 'Description:',
                'attr' => array(
                    'class' => 'form-control form-control--lg margin--b',
                    'placeholder' => 'Enter tag description'
                )
            ))
            ->add('type', HiddenType::class, array(
                'data' => Taxonomy::TYPE_TAG
            ))
            ->remove('parent');
    }

    public function getName()
    {
        return "edtag";
    }


}