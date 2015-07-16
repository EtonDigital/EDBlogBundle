<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 22.6.15.
 * Time: 08.44
 */

namespace ED\BlogBundle\Event;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\EventDispatcher\Event;

class MediaArrayEvent  extends Event
{
    protected $mediaArray;

    function __construct($mediaArray)
    {
        $this->mediaArray = $mediaArray;
    }

    /**
     * @return ArrayCollection
     */
    public function getMedia()
    {
        return $this->mediaArray;
    }
}