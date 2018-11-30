<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 7.5.15.
 * Time: 15.29
 */

namespace ED\BlogBundle\Security\Authorization\Voter;

use ED\BlogBundle\Security\ACL\PermissionMap;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class AdminVoter implements VoterInterface
{
    private $permissionMap;

    public function __construct(PermissionMap $permissionMap)
    {
        $this->permissionMap = $permissionMap;
    }

    /**
     * Checks if the voter supports the given attribute.
     *
     * @param string $attribute An attribute
     *
     * @return bool true if this Voter supports the attribute, false otherwise
     */
    public function supportsAttribute($attribute)
    {
        return $this->permissionMap->supports($attribute);
    }

    /**
     * Checks if the voter supports the given class.
     *
     * @param string $class A class name
     *
     * @return bool true if this Voter can process the class
     */
    public function supportsClass($class)
    {
        if($class)
        {
            $classNameArray = explode('\\', $class);
            $className = $classNameArray[ count($classNameArray)-1 ];

            if( in_array($className, array('User', 'AdminVoter')) )
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the vote for the given parameters.
     *
     * This method must return one of the following constants:
     * ACCESS_GRANTED, ACCESS_DENIED, or ACCESS_ABSTAIN.
     *
     * @param TokenInterface $token A TokenInterface instance
     * @param object|null $object The object to secure
     * @param array $attributes An array of attributes associated with the method being invoked
     *
     * @return int either ACCESS_GRANTED, ACCESS_ABSTAIN, or ACCESS_DENIED
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if ($object === null || !$this->supportsClass(get_class($class)))
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
            if(in_array('ADMINISTRATE_BLOG', $attributes) || in_array('SWITCH_ARTICLE_AUTHOR', $attributes))
            {
                if($user->hasRole('ROLE_BLOG_ADMIN'))
                {
                    return self::ACCESS_GRANTED;
                }
                else
                {
                    return self::ACCESS_DENIED;
                }
            }
            elseif( in_array('ADMINISTRATE_COMMENTS', $attributes) )
            {
                if($user->hasRole('ROLE_BLOG_ADMIN') || $user->hasRole('ROLE_BLOG_EDITOR'))
                {
                    return self::ACCESS_GRANTED;
                }
                else
                {
                    return self::ACCESS_DENIED;
                }
            }

            return self::ACCESS_ABSTAIN;

        }

    }

}
