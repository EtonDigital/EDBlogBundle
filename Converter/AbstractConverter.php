<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 15.7.15.
 * Time: 09.02
 */

namespace ED\BlogBundle\Converter;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AbstractConverter implements ParamConverterInterface
{
    protected $targetEntitiesArray;
    protected $doctrine;

    function __construct($targetEntitiesArray, Registry $doctrine)
    {
        $this->targetEntitiesArray = $targetEntitiesArray;
        $this->doctrine = $doctrine;
    }

    /**
     * Stores the object in the request.
     *
     * @param Request $request The request
     * @param ParamConverter $configuration Contains the name, class and options of the object
     *
     * @return bool True if the object has been successfully set, else false
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $slug = $request->get('slug', null);
        $username = $request->get('username', null);
        $id = $request->get('id', null);

        if ($slug)
        {
            $searchArray = array('slug' => $slug);
        }
        elseif($username)
        {
            $searchArray = array('username_canonical' => $username);
        }
        elseif($id)
        {
            $searchArray = array('id' => $id);
        }
        else
        {
            throw new \InvalidArgumentException('Route attribute is missing');
        }

        $class = $this->targetEntitiesArray[$configuration->getClass()];

        $repository = $this->doctrine->getRepository($class);

        $object = $repository->findOneBy( $searchArray );

        if(!$object)
            throw new NotFoundHttpException("Sorry, requested resource can't be found.");

        $request->attributes->set($configuration->getName(), $object);

    }

    /**
     * Checks if the object is supported.
     *
     * @param ParamConverter $configuration Should be an instance of ParamConverter
     *
     * @return bool True if the object is supported, else false
     */
    public function supports(ParamConverter $configuration)
    {
        if (null === $configuration->getClass()) {
            return false;
        }

        if(array_key_exists($configuration->getClass(), $this->targetEntitiesArray))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

}
