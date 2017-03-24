<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 16.6.15.
 * Time: 09.01
 */

namespace ED\BlogBundle\Model\Entity;

use Doctrine\ORM\Mapping as ORM;

class BlogSettings
{
    const COMMENTS_APPROVE_MANUAL = 1;
    const COMMENTS_APPROVE_AUTOMATIC = 0;
    const COMMENTS_ENABLED = 1;
    const COMMENTS_DISABLED = 0;
    const COMMENTS_PUBLIC_VISIBLE = 1;
    const COMMENTS_PUBLIC_HIDE = 0;
    const COMMENTS_ORDER_LATEST_BOTTOM = 'ASC';
    const COMMENTS_ORDER_LATEST_TOP = 'DESC';
    const COMMENTER_ACCESS_LEVEL_PUBLIC = "public";
    const COMMENTER_ACCESS_LEVEL_PRIVATE = "private";
    const DATE_FORMAT_1="F j, Y";
    const DATE_FORMAT_2="Y-m-d";
    const DATE_FORMAT_3="m/d/Y";
    const DATE_FORMAT_4="d/m/Y";
    const DATE_FORMAT_CUSTOM="Custom Date Format";
    const DATE_FORMAT_CUSTOM_FIELD="custom_date_format";
    const TIME_FORMAT_1="g:i a";
    const TIME_FORMAT_2="g:i A";
    const TIME_FORMAT_3="H:i";
    const TIME_FORMAT_CUSTOM="Custom Time Format";
    const TIME_FORMAT_CUSTOM_FIELD="custom_time_format";

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $property;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $value;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @param mixed $property
     */
    public function setProperty($property)
    {
        $this->property = $property;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }


}