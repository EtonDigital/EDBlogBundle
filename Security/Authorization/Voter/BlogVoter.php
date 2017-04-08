<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 7.5.15.
 * Time: 15.29
 */

namespace ED\BlogBundle\Security\Authorization\Voter;

use ED\BlogBundle\Model\Entity\BlogSettings;
use ED\BlogBundle\Model\Entity\Comment;
use FOS\UserBundle\Model\User;
use ED\BlogBundle\Handler\SettingsHandler;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class BlogVoter extends Voter
{
    const BLOG_COMMENTS_ENABLED = 'ACCESS_COMMENTS';
    const BLOG_COMMENTS_LIST = 'ACCESS_COMMENTS_LIST';
    const BLOG_CREATE_COMMENT = 'CREATE_COMMENT';

    private $blogSettings;

    public function __construct(SettingsHandler $settings)
    {
        $this->blogSettings = $settings;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::BLOG_COMMENTS_ENABLED, self::BLOG_COMMENTS_LIST, self::BLOG_CREATE_COMMENT])) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        // you know $subject is a Post object, thanks to supports
        /** @var Comment $comment */
        $comment = $subject;

        switch ($attribute) {
            case self::BLOG_COMMENTS_ENABLED:
                return $this->canComment($comment, $user);
            case self::BLOG_COMMENTS_LIST:
                return $this->canView($comment, $user);
            case self::BLOG_CREATE_COMMENT:
                return $this->canCreate($comment, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }


    private function canComment($comment, User $user)
    {
        return $this->blogSettings->commentsEnabled();
    }

    private function canView($comment, User $user)
    {
        if (!$user instanceof User && !$this->blogSettings->commentsPubliclyVisible()) {
            // the user must be logged in; if not, deny access
            return false;
        }

        return true;
    }

    private function canCreate($comment, User $user)
    {
        if( !$user instanceof User && !$this->blogSettings->publicUserCanComment())
        {
            return false;
        }

        return true;
    }

}