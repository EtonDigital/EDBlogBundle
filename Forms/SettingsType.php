<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 16.6.15.
 * Time: 10.35
 */

namespace ED\BlogBundle\Forms;


use ED\BlogBundle\Model\Entity\BlogSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class SettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $currentDate = new \DateTime();

        $builder
            ->add('comments_enabled', ChoiceType::class, array(
                'label' => 'The comments are: ',
                'expanded' => true,
                'choices' => array(
                    "Enabled" => BlogSettings::COMMENTS_ENABLED,
                    "Disabled" => BlogSettings::COMMENTS_DISABLED
                ),
                'data' => isset($options['data']['comments_enabled']) ? $options['data']['comments_enabled'] : null

            ))
            ->add('comments_visible_public', ChoiceType::class, array(
                'label' => 'The comments are publicly visible: ',
                'expanded' => true,
                'choices' => array(
                    "Enabled" => BlogSettings::COMMENTS_PUBLIC_VISIBLE,
                    "Disabled" => BlogSettings::COMMENTS_PUBLIC_HIDE
                ),
                'data' => isset($options['data']['comments_visible_public']) ? $options['data']['comments_visible_public'] : null
            ))
            ->add('commenter_access_level', ChoiceType::class, array(
                'label' => 'Who can write comments: ',
                'expanded' => true,
                'choices' => array(
                    "All users" => BlogSettings::COMMENTER_ACCESS_LEVEL_PUBLIC,
                    "Registered users" => BlogSettings::COMMENTER_ACCESS_LEVEL_PRIVATE
                ),
                'data' => isset($options['data']['commenter_access_level']) ? $options['data']['commenter_access_level'] : null
            ))
            ->add('comments_display_order', ChoiceType::class, array(
                'label' => 'Display comments: ',
                'expanded' => true,
                'choices' => array(
                    "Latest on the bottom" => BlogSettings::COMMENTS_ORDER_LATEST_BOTTOM,
                    "Latest on the top" => BlogSettings::COMMENTS_ORDER_LATEST_TOP
                ),
                'data' => isset($options['data']['comments_display_order']) ? $options['data']['comments_display_order'] : null
            ))
            ->add('comments_manual_approving', ChoiceType::class, array(
                'label' => 'Comments should be approved manually: ',
                'expanded' => true,
                'choices' => array(
                    "Enabled" => BlogSettings::COMMENTS_APPROVE_MANUAL,
                    "Disabled" => BlogSettings::COMMENTS_APPROVE_AUTOMATIC
                ),
                'data' => isset($options['data']['comments_manual_approving']) ? $options['data']['comments_manual_approving'] : null
            ))
            ->add('date_format', ChoiceType::class, array(
                'label' => 'Date format: ',
                'choices' => array(
                    $currentDate->format(BlogSettings::DATE_FORMAT_1) => BlogSettings::DATE_FORMAT_1,
                    $currentDate->format(BlogSettings::DATE_FORMAT_2) => BlogSettings::DATE_FORMAT_2,
                    $currentDate->format(BlogSettings::DATE_FORMAT_3) => BlogSettings::DATE_FORMAT_3,
                    $currentDate->format(BlogSettings::DATE_FORMAT_4) => BlogSettings::DATE_FORMAT_4,
                    BlogSettings::DATE_FORMAT_CUSTOM => BlogSettings::DATE_FORMAT_CUSTOM_FIELD
                ),
                'expanded' => true,
                'data' => isset($options['data']['date_format']) ?  $this->setCustomDateDefault($options['data']['date_format']) : null
            ))
            ->add(BlogSettings::DATE_FORMAT_CUSTOM_FIELD, TextType::class, array(
                'label' => isset($options['data']['date_format']) ? $options['data']['date_format'] : null,
                'required' => false,
                'data' => isset($options['data']['date_format'])  ? $options['data']['date_format'] : null
            ))
            ->add('time_format', ChoiceType::class, array(
                'label' => 'Time format ',
                'choices' => array(
                    $currentDate->format(BlogSettings::TIME_FORMAT_1) => BlogSettings::TIME_FORMAT_1,
                    $currentDate->format(BlogSettings::TIME_FORMAT_2) => BlogSettings::TIME_FORMAT_2,
                    $currentDate->format(BlogSettings::TIME_FORMAT_3) => BlogSettings::TIME_FORMAT_3,
                    BlogSettings::TIME_FORMAT_CUSTOM => BlogSettings::TIME_FORMAT_CUSTOM_FIELD
                ),
                'multiple' => false,
                'expanded' => true,

                'data' => isset($options['data']['time_format']) ? $this->setCustomTimeDefault($options['data']['time_format']) : null
            ))
            ->add(BlogSettings::TIME_FORMAT_CUSTOM_FIELD, TextType::class, array(
                'label' => isset($options['data']['time_format']) ? $options['data']['time_format'] : null,
                'required' => false,
                'data' => isset($options['data']['time_format']) ? $options['data']['time_format'] : null
            ))
            ->add('update', SubmitType::class, array(
                'attr' => array(
                    'class' => 'btn btn-md btn-primary btn-wide flright--responsive-mob margin--t margin--b first-in-line'
                )
            ));
    }

    private function setCustomDateDefault($value)
    {
        if (self::isValueInDateDefaults($value))
        {
            return $value;
        }else
        {
            return BlogSettings::DATE_FORMAT_CUSTOM_FIELD;
        }
    }

    private function setCustomTimeDefault($value)
    {
        if (self::isValueInTimeDefaults($value) )
        {
            return $value;
        }else
        {
            return BlogSettings::TIME_FORMAT_CUSTOM_FIELD;
        }
    }

    private function isValueInDateDefaults($value)
    {
        if($value==BlogSettings::DATE_FORMAT_1 || $value==BlogSettings::DATE_FORMAT_2 || $value==BlogSettings::DATE_FORMAT_3 || $value==BlogSettings::DATE_FORMAT_4)
        {
            return true;
        }else
        {
            return false;
        }
    }

    private function isValueInTimeDefaults($value)
    {
        if ($value==BlogSettings::TIME_FORMAT_1 || $value==BlogSettings::TIME_FORMAT_2 || $value==BlogSettings::TIME_FORMAT_3 )
        {
            return true;
        }else
        {
            return false;
        }
    }


    public function getBlockPrefix()
    {
        return "edblog_settings";
    }

}