<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 18.6.15.
 * Time: 10.50
 */

namespace ED\BlogBundle\Listener;


use ED\BlogBundle\Event\CommentEvent;
use ED\BlogBundle\Events\EDBlogEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class CommentEventListener implements EventSubscriberInterface
{
    protected $session;

    function __construct(Session $session)
    {
        $this->session = $session;
    }


    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array(
            EDBlogEvents::ED_BLOG_COMMENT_CREATED => "commentCreated"
        );
    }

    public function commentCreated(CommentEvent $event)
    {
        $comment = $event->getComment();

        //Save created comments in session
        //We need info if public user is creator of a comment
        $createdComments = $this->session->get('sessionComments', false);

        if(!$createdComments)
        {
            $createdComments = array();
        }
        else
        {
            $createdComments = unserialize($createdComments);
        }


        $createdComments[] = $comment->getId();

        $this->session->set('sessionComments', serialize($createdComments));
    }

}