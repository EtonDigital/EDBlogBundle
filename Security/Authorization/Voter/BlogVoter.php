<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 7.5.15.
 * Time: 15.29
 */

namespace ED\BlogBundle\Security\Authorization\Voter;

use ED\BlogBundle\Handler\SettingsHandler;
use ED\BlogBundle\Security\ACL\PermissionMap;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class BlogVoter implements VoterInterface
{
    private $permissionMap;
    private $blogSettings;

    public function __construct(PermissionMap $permissionMap, SettingsHandler $settings)
    {
        $this->permissionMap = $permissionMap;
        $this->blogSettings = $settings;
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

    public function supportsClass($object)
    {
        return (!$object || $object instanceof UserInterface ) ? true : false;
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
        $user = $token->getUser();

        if(in_array('ACCESS_COMMENTS', $attributes))
        {
            if($this->blogSettings->commentsEnabled())
            {
                return self::ACCESS_GRANTED;
            }
            else
            {
                return self::ACCESS_DENIED;
            }
        }
        elseif(in_array('ACCESS_COMMENTS_LIST', $attributes))
        {
            if( $user === 'anon.' && !$this->blogSettings->commentsPubliclyVisible())
            {
                return self::ACCESS_DENIED;
            }
            else
            {
                return self::ACCESS_GRANTED;
            }
        }
        elseif(in_array('CREATE_COMMENT', $attributes))
        {
            if( $user === 'anon.' && !$this->blogSettings->publicUserCanComment())
            {
                return self::ACCESS_DENIED;
            }
            else
            {
                return self::ACCESS_GRANTED;
            }
        }
        else
        {
            return self::ACCESS_ABSTAIN;
        }
    }

}