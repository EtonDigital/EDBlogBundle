<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 18.6.15.
 * Time: 10.48
 */

namespace ED\BlogBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class CommentEvent extends Event
{
    protected $comment;

    function __construct($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }
}