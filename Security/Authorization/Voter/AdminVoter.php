<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 7.5.15.
 * Time: 15.29
 */

namespace ED\BlogBundle\Security\Authorization\Voter;

use FOS\UserBundle\Model\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AdminVoter extends Voter
{
    const BLOG_ADMIN = 'ADMINISTRATE_BLOG';
    const BLOG_ADMIN_COMMENTS = 'ADMINISTRATE_COMMENTS';
    const BLOG_SWITCH_AUTHOR = 'SWITCH_ARTICLE_AUTHOR';

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::BLOG_ADMIN, self::BLOG_ADMIN_COMMENTS, self::BLOG_SWITCH_AUTHOR])) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        switch ($attribute) {
            case self::BLOG_ADMIN:
                return $this->canAdminister($user);
            case self::BLOG_ADMIN_COMMENTS:
                return $this->canAdminComments($user);
            case self::BLOG_SWITCH_AUTHOR:
                return $this->canAdminister($user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canAdminister($user)
    {
        return $user->hasRole('ROLE_BLOG_ADMIN');
    }

    private function canAdminComments($user)
    {
        return ($user->hasRole('ROLE_BLOG_ADMIN') || $user->hasRole('ROLE_BLOG_EDITOR'));
    }
}