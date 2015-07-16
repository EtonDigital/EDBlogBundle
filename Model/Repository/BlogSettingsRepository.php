<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 16.6.15.
 * Time: 11.17
 */

namespace ED\BlogBundle\Model\Repository;

use Doctrine\ORM\EntityRepository;
use ED\BlogBundle\Interfaces\Repository\BlogSettingsRepositoryInterface;

class BlogSettingsRepository extends EntityRepository implements BlogSettingsRepositoryInterface
{
    public function getSettingsArray()
    {
        $settings = $this->createQueryBuilder('s')
            ->select('s.property')
            ->addSelect('s.value')
            ->getQuery();

        return $settings->getResult('SettingsHydrator');
    }

    public function removeAll()
    {
        $blogSettings = $this->_entityName;
        $q = $this->getEntityManager()
            ->createQuery("delete from $blogSettings");
        $numDeleted = $q->execute();
        return $numDeleted;
    }
}