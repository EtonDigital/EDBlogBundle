<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 16.6.15.
 * Time: 12.19
 */

namespace ED\BlogBundle\Handler;

use Doctrine\Bundle\DoctrineBundle\Registry;
use ED\BlogBundle\Model\Entity\BlogSettings;

class SettingsHandler
{
    private $doctrine;
    private $settingsClass;

    function __construct(Registry $doctrine, $settingsClass)
    {
        $this->doctrine = $doctrine;
        $this->settingsClass = $settingsClass;
    }

    public function saveSettings($settingsInput)
    {
        try
        {
            $em = $this->doctrine->getManager();
            $counter = 0;

            $settings=$this->modifySeetingsToAcceptCustomField($settingsInput);


            foreach ($settings as $key => $value)
            {
                $settings = $this->doctrine->getRepository($this->settingsClass)->findOneBy(array('property' => $key));

                if (!$settings)
                {
                    $class = $this->settingsClass;
                    $settings = new $class();
                    $settings
                        ->setProperty($key);
                }

                $settings
                    ->setValue($value);

                $em->persist($settings);
                $counter++;

                if ($counter == 50)
                {
                    $em->flush();
                    $counter = 0;
                }
            }

            $em->flush();
        }
        catch(\Exception $e)
        {
            return $e->getMessage();
        }

        return true;
    }

    public function commentsEnabled()
    {
        return $this->isSettingsLikeValue('comments_enabled', BlogSettings::COMMENTS_ENABLED);
    }

    public function commentsPubliclyVisible()
    {
        return $this->isSettingsLikeValue('comments_visible_public', BlogSettings::COMMENTS_PUBLIC_VISIBLE);
    }

    public function publicUserCanComment()
    {
        return $this->isSettingsLikeValue('commenter_access_level', BlogSettings::COMMENTER_ACCESS_LEVEL_PUBLIC);
    }

    public function manualCommentsApprove()
    {
        return $this->isSettingsLikeValue('comments_manual_approving', BlogSettings::COMMENTS_APPROVE_MANUAL);
    }

    public function getCommentsDisplayOrder()
    {
        $settings = $this->doctrine->getRepository($this->settingsClass)->findOneBy(array('property' => "comments_display_order"));

        if ($settings)
        {
            return $settings->getValue();
        }
        else
        {
            return "DESC";
        }
    }

    private function isSettingsLikeValue($property, $expectedValue)
    {
        $result = false;
        $settings = $this->doctrine->getRepository( $this->settingsClass )->findOneBy(array('property' => $property));

        if($settings && $settings->getValue() == $expectedValue)
        {
            $result = true;
        }

        return $result;
    }

    public function getDatetimeFormat()
    {
        return $this->getSettingBlogDateFormat() . ' ' . $this->getSettingBlogTimeFormat();
    }

    public function getSettingBlogDateFormat()
    {
        $settings = $this->doctrine->getRepository( $this->settingsClass )->findOneBy(array('property' => 'date_format'));
        if ($settings)
        {
            return $settings->getValue();
        }else
        {
            return BlogSettings::DATE_FORMAT_1;
        }
    }

    public function getSettingBlogTimeFormat()
    {
        $settings = $this->doctrine->getRepository( $this->settingsClass )->findOneBy(array('property' => 'time_format'));
        if ($settings)
        {
            return $settings->getValue();
        }else
        {
            return BlogSettings::TIME_FORMAT_1;
        }
    }

    private function modifySeetingsToAcceptCustomField($settings)
    {
        if(isset($settings['date_format']) && $settings['date_format']=="custom_date_format")
        {
            $settings['date_format']=$settings['custom_date_format'];
        }
        unset($settings['custom_date_format']);

        if(isset($settings['time_format']) && $settings['time_format']=="custom_time_format")
        {
            $settings['time_format']=$settings['custom_time_format'];
        }
        unset($settings['custom_time_format']);

        return $settings;
    }
}