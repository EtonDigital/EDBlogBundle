<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 25.5.15.
 * Time: 14.48
 */

namespace ED\BlogBundle\Forms;


use Doctrine\ORM\EntityRepository;
use ED\BlogBundle\Forms\TermType;
use ED\BlogBundle\Model\Entity\Taxonomy;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaxonomyType extends AbstractType
{
    protected $dataClass;

    /**
     * @param $className of the form object
     */
    function __construct($dataClass)
    {
        $this->dataClass = $dataClass;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $object = $builder->getData();

        $builder
            ->add('term', TermType::class, array(
                'required' => true,
                'label' => 'Title:',
                'attr' => array(
                    'class' => 'form-control form-control--lg margin--b',
                    'placeholder' => 'Enter title of the category'
                )
            ))
            ->add('description', TextType::class, array(
                'required' => false,
                'label' => 'Description:',
                'attr' => array(
                    'class' => 'form-control form-control--lg margin--b',
                    'placeholder' => 'Enter description of the category'
                )
            ))
            ->add('parent', EntityType::class, array(
                'label' => 'Parent category:',
                'required' => false,
                'class' => $this->dataClass,
                'placeholder' => 'Select parent category',
                'query_builder' => function (EntityRepository $er) use ($object) {
                    if($object && $object->getId())
                    {
                        return $er->createQueryBuilder('c')
                            ->where('c.type = :type')
                            ->andWhere('c <> :object')
                            ->orderBy('c.id', 'DESC')
                            ->setParameter('type', Taxonomy::TYPE_CATEGORY)
                            ->setParameter('object', $object);
                    }
                    else
                    {
                        return $er->createQueryBuilder('c')
                            ->where('c.type = :type')
                            ->orderBy('c.id', 'DESC')
                            ->setParameter('type', Taxonomy::TYPE_CATEGORY);
                    }
                },
                'attr' => array(
                    'class' => 'form-control form-control--lg margin--b hide js-get-pretty-categories',
                    'data-empty-option' => 'Select parent category'
                )
            ))
            ->add('type', HiddenType::class, array(
                'data' => Taxonomy::TYPE_CATEGORY
            ))
            ->add('save', SubmitType::class, array(
                'attr' => array(
                    'class' => 'btn btn-md btn-primary btn-wide--xl flright--responsive-mob margin--t margin--b first-in-line'
                )
            ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getBlockPrefix()
    {
        return "edtaxonomy";
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->dataClass,
        ));
    }

}