<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 16.6.15.
 * Time: 09.16
 */

namespace ED\BlogBundle\Interfaces\Model;


interface BlogSettingsInterface
{
    public function getValue();

    public function getProperty();
}