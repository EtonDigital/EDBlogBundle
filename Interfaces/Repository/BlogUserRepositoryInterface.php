<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 3.7.15.
 * Time: 14.39
 */

namespace ED\BlogBundle\Interfaces\Repository;


interface BlogUserRepositoryInterface
{
    /**
     * Number of active users per author
     *
     * @param $user
     * @return mixed
     */
    public function getNumberOfActiveBlogs($user);

    /**
     * Query for Blog administration list page
     *
     * @param $orderBy
     * @param $order
     * @return mixed
     */
    public function getSortableQuery($orderBy, $order);
}