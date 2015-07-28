<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 16.6.15.
 * Time: 11.16
 */

namespace ED\BlogBundle\Interfaces\Repository;

interface BlogSettingsRepositoryInterface
{
    public function getSettingsArray();

    public function removeAll();
}