<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 16.6.15.
 * Time: 11.50
 */

namespace ED\BlogBundle\Hydrators;

use Doctrine\ORM\Internal\Hydration\AbstractHydrator;
use PDO;

class SettingsHydrator extends AbstractHydrator
{
    /**
     * Hydrates all rows from the current statement instance at once.
     *
     * @return array
     */
    protected function hydrateAllData()
    {
        $result = array();

        $data =  $this->_stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($data as $row)
        {
            $keys = array_keys($row);

            $result[ $row[ $keys[0] ] ] = $row[ $keys[1] ];
        }

        return $result;
    }

}