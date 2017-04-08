<?php

namespace ED\BlogBundle\Forms;

use FOS\UserBundle\Form\Type\RegistrationFormType as FOSRegistrationFormType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Validator\Constraints\IsTrue;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('firstName');
        $builder->add('lastName');

        $builder->add('termsAccepted', CheckboxUrlLabelType::class, array(
            'mapped' => false,
            'label' => 'Accept Terms',
            'constraints' => new IsTrue(['message' => 'You have to accept our terms']),
            'routes' => ['%url%' => ['name' => 'account_terms']]
        ));
    }

    public function getParent()
    {
        return FOSRegistrationFormType::class;
    }

    public function getBlockPrefix()
    {
        return 'app_user_registration';
    }

//    public function finishView(FormView $view, FormInterface $form, array $options)
//    {
//        $view->vars['name'] = 'registration';
//    }
}