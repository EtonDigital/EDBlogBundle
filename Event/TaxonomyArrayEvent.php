<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 3.6.15.
 * Time: 15.51
 */

namespace ED\BlogBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class TaxonomyArrayEvent extends Event
{
    protected $taxonomies;

    function __construct($taxonomies=array())
    {
        $this->taxonomies = $taxonomies;
    }

    /**
     * @return array
     */
    public function getTaxonomies()
    {
        return $this->taxonomies;
    }

}