<?php
/**
 * Created by Eton Digital.
 * User: Milos Milojevic (milos.milojevic@etondigital.com)
 * Date: 6/2/15
 * Time: 1:20 PM
 */

namespace ED\BlogBundle\Forms;

use FOS\UserBundle\Event\FormEvent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CommentType extends AbstractType
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
        $user = $object->getAuthor();

        $builder
            ->add('comment', 'textarea', array(
                'required' => true,
                'label' => 'Comment:',
                'attr' => array(
                    'class' => 'form-control form-control--lg margin--b',
                    'rows'  => 4,
                    'placeholder' => 'Enter your comment'
                )
            ))
            ->add('save', 'submit', array(
                'label' => 'Submit comment',
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
                            'placeholder' => 'Enter your name and surname'
                        )
                    ))
                    ->add('email', 'email', array(
                        'required' => true,
                        'label' => 'Email:',
                        'attr' => array(
                            'class' => 'form-control form-control--lg margin--b',
                            'placeholder' => 'Enter your email address'
                        )
                    ))
                    ->add('username', 'text', array(
                        'required' => false,
                        'mapped' => false,
                        'attr' => array(
                            'class' => 'hidden'
                        )
                    ));
            }
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function($event){
            $form = $event->getForm();

            if(isset($form['username']))
            {
                $spamTester = $form['username']->getData();

                if ($spamTester)
                {
                    $form->addError(new FormError("We are sorry, but commenting is currently unavailable."));
                }
            }
        });
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return "edcomment";
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->dataClass,
        ));
    }

}