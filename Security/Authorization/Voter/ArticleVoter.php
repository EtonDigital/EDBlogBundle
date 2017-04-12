<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 2.7.15.
 * Time: 09.00
 */

namespace ED\BlogBundle\Security\Authorization\Voter;

use FOS\UserBundle\Model\User;
use ED\BlogBundle\Interfaces\Model\ArticleInterface;
use ED\BlogBundle\Model\Entity\Article;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ArticleVoter extends Voter
{
    // these strings are just invented: you can use anything
    const VIEW = 'view';
    const EDIT = 'EDIT_ARTICLE';
    const PUBLISH = 'PUBLISH_ARTICLE';
    const EDIT_PUBLISH = 'EDIT_PUBLISH_STATUS';

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, array(self::VIEW, self::EDIT, self::PUBLISH, self::EDIT_PUBLISH))) {
            return false;
        }

        // only vote on Post objects inside this voter
        if (!$subject instanceof Article) {
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

        // you know $subject is a Post object, thanks to supports
        /** @var Article $article */
        $article = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($article, $user);
            case self::EDIT:
                return $this->canEdit($article, $user);
            case self::PUBLISH:
                return $this->canPublish($article, $user);
            case self::EDIT_PUBLISH:
                return $this->canEditPublish($article, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canView(Article $article, User $user)
    {
        // if they can edit, they can view
        if ($this->canEdit($article, $user)) {
            return true;
        }

        // the Post object could have, for example, a method isPrivate()
        // that checks a boolean $private property
        return !$article->getPublishedAt();
    }

    private function canEdit(Article $article, User $user)
    {
        if($user->hasRole('ROLE_BLOG_ADMIN') || $user->hasRole('ROLE_BLOG_EDITOR'))
        {
            return true;
        }
        else
        {
            if($user->hasRole('ROLE_BLOG_AUTHOR') || $user->hasRole('ROLE_BLOG_CONTRIBUTOR'))
            {
                if($article instanceof ArticleInterface && $article->getAuthor() == $user)
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }
        }
    }

    private function canPublish(Article $article, User $user)
    {
        if($user->hasRole('ROLE_BLOG_ADMIN') || $user->hasRole('ROLE_BLOG_EDITOR'))
        {
            return true;
        }
        else
        {
            if($user->hasRole('ROLE_BLOG_AUTHOR'))
            {
                if($article instanceof ArticleInterface && $article->getAuthor() == $user)
                {
                    return true;
                }
            }

            return false;
        }
    }

    private function canEditPublish(Article $article, User $user)
    {
        $testObject = $article->getParent() ? $article->getParent() : $article;

        if($user->hasRole('ROLE_BLOG_CONTRIBUTOR'))
        {
            return true;
        }
        elseif( $testObject && $testObject->getStatus() != Article::STATUS_PUBLISHED )
        {
            return false;
        }
        else
        {
            return true;
        }
    }
}