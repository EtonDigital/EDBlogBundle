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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
            ->add('comment', 'textarea', array(
                'required' => true,
                'label' => 'Comment:',
                'attr' => array(
                    'class' => 'form-control form-control--lg margin--b',
                    'rows'  => 4,
                    'placeholder' => 'Edit comment'
                )
            ))
            ->add('status', 'choice', array(
                'label' => 'Status: ',
                'choices' => array(
                    Comment::STATUS_PENDING => "Pending",
                    Comment::STATUS_ACTIVE => "Active"
                ),
                'attr' => array(
                    'class' => 'form-control form-control--lg margin--b color-placeholder'
                )
            ))
            ->add('update', 'submit', array(
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
                    ->add('name', 'text', array(
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

    public function setDefaultOptions(OptionsResolverInterface $resolver)
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
    public function getName()
    {
        return "ed_blog_comment";
    }


}