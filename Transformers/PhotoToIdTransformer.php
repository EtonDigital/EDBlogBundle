<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 9.6.15.
 * Time: 14.09
 */

namespace ED\BlogBundle\Transformers;


use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class PhotoToIdTransformer implements DataTransformerInterface
{
    private $om;

    function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function transform($value)
    {
        return $value ? $value->getId() : '';
    }
    public function reverseTransform($value)
    {
        if($value)
        {
            $photo = $this->om->getRepository('ApplicationSonataMediaBundle:Media')->find($value);

            if ($photo)
            {
                return $photo;
            }
            else
            {
                return null;
            }
        }
        else
        {
            return null;
        }
    }
}