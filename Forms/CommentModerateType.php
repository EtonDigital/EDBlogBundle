<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 18.6.15.
 * Time: 14.23
 */

namespace ED\BlogBundle\Forms;

use ED\BlogBundle\Model\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentModerateType extends AbstractType
{
    protected $dataClass;

    function __construct($dataClass)
    {
        $this->dataClass = $dataClass;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('comment', TextareaType::class, array(
                'required' => true,
                'label' => 'Comment:',
                'attr' => array(
                    'class' => 'form-control form-control--lg margin--b',
                    'rows'  => 4,
                    'placeholder' => 'Edit comment'
                )
            ))
            ->add('status', ChoiceType::class, array(
                'label' => 'Status: ',
                'choices' => array(
                    Comment::STATUS_PENDING => "Pending",
                    Comment::STATUS_ACTIVE => "Active"
                ),
                'attr' => array(
                    'class' => 'form-control form-control--lg margin--b color-placeholder'
                )
            ))
            ->add('update', SubmitType::class, array(
                'label' => 'Update',
                'attr' => array(
                    'class' => 'btn btn-md btn-primary btn-wide flright--responsive-mob margin--t margin--b first-in-line'
                )
            ));

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function($event){
            $comment = $event->getData();
            $form = $event->getForm();

            if(!$comment->getAuthor())
            {
                $form
                    ->add('name', TextType::class, array(
                        'required' => true,
                        'label' => 'Display name:',
                        'attr' => array(
                            'class' => 'form-control form-control--lg margin--b',
                            'placeholder' => 'Enter display name'
                        )
                    ));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->dataClass,
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getBlockPrefix()
    {
        return "ed_blog_comment";
    }


}