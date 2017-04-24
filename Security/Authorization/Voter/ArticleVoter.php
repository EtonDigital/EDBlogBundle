<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 2.7.15.
 * Time: 09.00
 */

namespace ED\BlogBundle\Security\Authorization\Voter;


use ED\BlogBundle\Interfaces\Model\ArticleInterface;
use ED\BlogBundle\Model\Entity\Article;
use ED\BlogBundle\Security\ACL\ArticlePermissionMap;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ArticleVoter implements VoterInterface
{
    private $permissionMap;

    function __construct(ArticlePermissionMap $permissionMap)
    {
        $this->permissionMap = $permissionMap;
    }


    public function supportsAttribute($attribute)
    {
        return $this->permissionMap->supports($attribute);
    }

    public function supportsClass($class)
    {
        try
        {
            $rc1 = new \ReflectionClass($class);
            return in_array(ArticleInterface::class, $rc1->getInterfaceNames(), true);
        }
        catch(\Exception $e)
        {
            return false;
        }
    }

    public function vote(TokenInterface $token, $object, array $attributes)
    {
        $class = get_class($object);

        if (!$this->supportsClass($class))
        {
            return self::ACCESS_ABSTAIN;
        }

        $user = $token->getUser();

        if($user === 'anon.')
        {
            return self::ACCESS_ABSTAIN;
        }
        else
        {
            if(in_array('EDIT_ARTICLE', $attributes))
            {
                if($user->hasRole('ROLE_BLOG_ADMIN') || $user->hasRole('ROLE_BLOG_EDITOR'))
                {
                    return self::ACCESS_GRANTED;
                }
                else
                {
                    if($user->hasRole('ROLE_BLOG_AUTHOR') || $user->hasRole('ROLE_BLOG_CONTRIBUTOR'))
                    {
                        if($object instanceof ArticleInterface && $object->getAuthor() == $user)
                        {
                            return self::ACCESS_GRANTED;
                        }
                        else
                        {
                            return self::ACCESS_DENIED;
                        }
                    }
                }
            }
            elseif(in_array('PUBLISH_ARTICLE', $attributes))
            {
                if($user->hasRole('ROLE_BLOG_ADMIN') || $user->hasRole('ROLE_BLOG_EDITOR'))
                {
                    return self::ACCESS_GRANTED;
                }
                else
                {
                    if($user->hasRole('ROLE_BLOG_AUTHOR'))
                    {
                        if($object instanceof ArticleInterface && $object->getAuthor() == $user)
                        {
                            return self::ACCESS_GRANTED;
                        }
                    }

                    return self::ACCESS_DENIED;
                }
            }
            elseif(in_array('EDIT_PUBLISH_STATUS', $attributes) && $object instanceof ArticleInterface)
            {
                $testObject = $object->getParent() ? $object->getParent() : $object;

                if($user->hasRole('ROLE_BLOG_CONTRIBUTOR'))
                {
                    return self::ACCESS_DENIED;
                }
                elseif( $testObject && $testObject->getStatus() != Article::STATUS_PUBLISHED )
                {
                    return self::ACCESS_DENIED;
                }
                else
                {
                    return self::ACCESS_GRANTED;
                }
            }

        }
    }

}