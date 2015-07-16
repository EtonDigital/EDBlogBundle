<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 26.6.15.
 * Time: 15.29
 */

namespace ED\BlogBundle\Transformers;


use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class UserToEmailTransformer implements DataTransformerInterface
{
    private $om;
    private $objectClass;

    function __construct(ObjectManager $om, $objectClass)
    {
        $this->om = $om;
        $this->objectClass = $objectClass;
    }

    public function transform($value)
    {
        return !empty($value) ?  $value->getEmail() : '';
    }

    public function reverseTransform($value)
    {
        if($value)
        {
            $user = $this->om->getRepository($this->objectClass)->findOneBy(array('email' => $value));

            if (!$user)
                throw new TransformationFailedException();

            return $user;
        }
    }
}