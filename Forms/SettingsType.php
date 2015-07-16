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
use Symfony\Component\Form\FormBuilderInterface;

class SettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $currentDate = new \DateTime();

        $builder
            ->add('comments_enabled', 'choice', array(
                'label' => 'The comments are: ',
                'expanded' => true,
                'choices' => array(
                    BlogSettings::COMMENTS_ENABLED => "Enabled",
                    BlogSettings::COMMENTS_DISABLED => "Disabled"
                ),
                'data' => isset($options['data']['comments_enabled']) ? $options['data']['comments_enabled'] : null

            ))
            ->add('comments_visible_public', 'choice', array(
                'label' => 'The comments are publicly visible: ',
                'expanded' => true,
                'choices' => array(
                    BlogSettings::COMMENTS_PUBLIC_VISIBLE => "Enabled",
                    BlogSettings::COMMENTS_PUBLIC_HIDE => "Disabled"
                ),
                'data' => isset($options['data']['comments_visible_public']) ? $options['data']['comments_visible_public'] : null
            ))
            ->add('commenter_access_level', 'choice', array(
                'label' => 'Who can write comments: ',
                'expanded' => true,
                'choices' => array(
                    BlogSettings::COMMENTER_ACCESS_LEVEL_PUBLIC => "All users",
                    BlogSettings::COMMENTER_ACCESS_LEVEL_PRIVATE => "Registered users"
                ),
                'data' => isset($options['data']['commenter_access_level']) ? $options['data']['commenter_access_level'] : null
            ))
            ->add('comments_display_order', 'choice', array(
                'label' => 'Display comments: ',
                'expanded' => true,
                'choices' => array(
                    BlogSettings::COMMENTS_ORDER_LATEST_BOTTOM => "Latest on the bottom",
                    BlogSettings::COMMENTS_ORDER_LATEST_TOP => "Latest on the top"
                ),
                'data' => isset($options['data']['comments_display_order']) ? $options['data']['comments_display_order'] : null
            ))
            ->add('comments_manual_approving', 'choice', array(
                'label' => 'Comments should be approved manually: ',
                'expanded' => true,
                'choices' => array(
                    BlogSettings::COMMENTS_APPROVE_MANUAL => "Enabled",
                    BlogSettings::COMMENTS_APPROVE_AUTOMATIC => "Disabled"
                ),
                'data' => isset($options['data']['comments_manual_approving']) ? $options['data']['comments_manual_approving'] : null
            ))
            ->add('date_format', 'choice', array(
                'label' => 'Date format: ',
                'choices' => array(
                    BlogSettings::DATE_FORMAT_1 => $currentDate->format(BlogSettings::DATE_FORMAT_1),
                    BlogSettings::DATE_FORMAT_2 => $currentDate->format(BlogSettings::DATE_FORMAT_2),
                    BlogSettings::DATE_FORMAT_3 => $currentDate->format(BlogSettings::DATE_FORMAT_3),
                    BlogSettings::DATE_FORMAT_4 => $currentDate->format(BlogSettings::DATE_FORMAT_4),
                    'custom_date_format'=> false
                ),
                'expanded' => true,
                'data' => isset($options['data']['date_format']) ?  $this->setCustomDateDefault($options['data']['date_format']) : null
            ))
            ->add('custom_date_format', 'text', array(
                'label' => isset($options['data']['date_format']) ? $currentDate->format($options['data']['date_format']) : null,
                'required' => false,
                'data' => isset($options['data']['date_format'])  ? $options['data']['date_format'] : null
            ))
            ->add('time_format', 'choice', array(
                'label' => 'Time format ',
                'choices' => array(
                    BlogSettings::TIME_FORMAT_1 => $currentDate->format(BlogSettings::TIME_FORMAT_1),
                    BlogSettings::TIME_FORMAT_2 => $currentDate->format(BlogSettings::TIME_FORMAT_2),
                    BlogSettings::TIME_FORMAT_3 => $currentDate->format(BlogSettings::TIME_FORMAT_3),
                    'custom_time_format'=> false
                ),
                'multiple' => false,
                'expanded' => true,

                'data' => isset($options['data']['time_format']) ? $this->setCustomTimeDefault($options['data']['time_format']) : null
            ))
            ->add('custom_time_format', 'text', array(
                'label' => isset($options['data']['time_format']) ? $currentDate->format($options['data']['time_format']) : null,
                'required' => false,
                'data' => isset($options['data']['time_format']) ? $options['data']['time_format'] : null
            ))
            ->add('update', 'submit', array(
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
            return 'custom_date_format';
        }
    }

    private function setCustomTimeDefault($value)
    {
        if (self::isValueInTimeDefaults($value) )
        {
            return $value;
        }else
        {
            return 'custom_time_format';
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


    public function getName()
    {
        return "edblog_settings";
    }

}