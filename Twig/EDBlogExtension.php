<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 29.5.15.
 * Time: 13.18
 */

namespace ED\BlogBundle\Twig;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityRepository;
use ED\BlogBundle\Handler\BlogUserHandler;
use ED\BlogBundle\Handler\SettingsHandler;
use ED\BlogBundle\Interfaces\Model\ArticleInterface;
use ED\BlogBundle\Interfaces\Model\BlogTaxonomyInterface;
use ED\BlogBundle\Interfaces\Model\CommentInterface;
use ED\BlogBundle\Util\IDEncrypt;
use Symfony\Component\HttpFoundation\Session\Session;

class EDBlogExtension extends \Twig_Extension
{
    private $doctrine;
    private $userRepo;
    private $articleRepo;
    private $session;
    private $blogSettings;
    private $commentClass;
    private $blogUserHandler;

    public function __construct(Registry $doctrine, EntityRepository $userRepo, EntityRepository $articleRepo, Session $session, SettingsHandler $blogSettings, $commentClass, BlogUserHandler $blogUserHandler)
    {
        $this->doctrine = $doctrine;
        $this->userRepo=$userRepo;
        $this->articleRepo = $articleRepo;
        $this->session = $session;
        $this->blogSettings = $blogSettings;
        $this->commentClass = $commentClass;
        $this->blogUserHandler = $blogUserHandler;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('encrypt', array($this, 'encrypt')),
            new \Twig_SimpleFilter('showDate', array($this, 'showDate')),
            new \Twig_SimpleFilter('categoryLevelSlash', array($this, 'categoryLevelSlash')),
            new \Twig_SimpleFilter('blogTime', array($this, 'blogTime')),
            new \Twig_SimpleFilter('blogDate', array($this, 'blogDate')),
            new \Twig_SimpleFilter('blogDateTime', array($this, 'blogDateTime')),
            new \Twig_SimpleFilter('displayLinks', array($this, 'displayLinks')),
            new \Twig_SimpleFilter('blogRole', array($this, 'blogRole')),
        );
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('getMonth', array($this, 'getMonth')),
            new \Twig_SimpleFunction('categoriesFromFirstLevel', array($this, 'categoriesFromFirstLevel')),
            new \Twig_SimpleFunction('numberOfPublishedPosts', array($this, 'numberOfPublishedPosts')),
            new \Twig_SimpleFunction('getSortedOrderClass', array($this, 'getSortedOrderClass')),
            new \Twig_SimpleFunction('commentsEnabled', array($this, 'commentsEnabled')),
            new \Twig_SimpleFunction('commentsPubliclyVisible', array($this, 'commentsPubliclyVisible')),
            new \Twig_SimpleFunction('isMyComment', array($this, 'isMyComment')),
            new \Twig_SimpleFunction('commentsCount', array($this, 'commentsCount'))
        );
    }


    public function getName()
    {
        return "ed_blog_extension";
    }

    public function encrypt($id)
    {
        $return = IDEncrypt::encrypt($id);
        return $return;
    }

    public function showDate(\DateTime $date, $format = 'd.m.Y')
    {
        return $date->format($format);
    }

    public function categoryLevelSlash(BlogTaxonomyInterface $category)
    {
        $slash="";
        $parent = $category;

        while($parent = $parent->getParent())
        {
            $slash .= "-";
        }

        return $slash . ' ' .  $category->getTerm()->getTitle();
    }

    public function blogTime(\DateTime $date)
    {
        $format = $this->blogSettings->getSettingBlogTimeFormat();

        return $date->format($format);
    }

    public function blogDate($date)
    {
        if ($date instanceof \DateTime)
        {
            $format = $this->blogSettings->getSettingBlogDateFormat();

            return $date->format($format);
        }
        else
        {
            return '(not available)';
        }
    }

    public function blogDateTime(\DateTime $date)
    {
        $formatTime = $this->blogSettings->getSettingBlogTimeFormat();
        $formatDate = $this->blogSettings->getSettingBlogDateFormat();

        return $date->format("$formatDate $formatTime");
    }

    //Functions
    public function getMonth($month)
    {
        $stringmonth="";

        switch($month)
        {
            case "1" :
                $stringmonth = "January";
                break;
            case "2" :
                $stringmonth = "February";
                break;
            case "3" :
                $stringmonth = "March";
                break;
            case "4" :
                $stringmonth = "April";
                break;
            case "5" :
                $stringmonth = "May";
                break;
            case "6" :
                $stringmonth = "June";
                break;
            case "7" :
                $stringmonth = "July";
                break;
            case "8" :
                $stringmonth = "August";
                break;
            case "9" :
                $stringmonth = "September";
                break;
            case "10" :
                $stringmonth = "October";
                break;
            case "11" :
                $stringmonth = "November";
                break;
            case "12" :
                $stringmonth = "December";
                break;
        }

        return $stringmonth;
    }

    public function categoriesFromFirstLevel(BlogTaxonomyInterface $category)
    {

        $categories=array();
        $parent = $category;

        while($parent = $parent->getParent())
        {
            $categories[]=$parent;
        }

        return array_reverse($categories);
    }

    public function numberOfPublishedPosts($user)
    {
        $number = $this->articleRepo->getNumberOfActiveBlogs($user);

        return $number;
    }

    public function getSortedOrderClass($orderBy,$order,$thTitle)
    {
        $class="";
        if ($orderBy && $orderBy==$thTitle)
        {
            if ($order && $order=='asc')
            {
                $class="sort sort--asc";
            }else
            {
                $class="sort sort--desc";
            }
        }
        return $class;
    }

    public function commentsEnabled()
    {
        return $this->blogSettings->commentsEnabled();
    }

    public function commentsPubliclyVisible()
    {
        return $this->blogSettings->commentsPubliclyVisible();
    }

    public function isMyComment(CommentInterface $comment)
    {
        $myComments = $this->session->get('sessionComments', false);

        if(!$myComments)
        {
            return false;
        }
        else
        {
            $myComments = unserialize($myComments);
        }

        return in_array( $comment->getId(), $myComments );
    }

    public function commentsCount(ArticleInterface $article)
    {
        $result = $this->doctrine->getRepository( $this->commentClass )->findCountByArticle($article);

        return $result;
    }

    public function displayLinks($value)
    {
        $value = strip_tags($value);
        //match valid url
        $urlRegEx="/\(?(?:(http|https|ftp):\/\/)?(?:((?:[^\W\s]|\.|-|[:]{1})+)@{1})?((?:www.)?(?:[^\W\s]|\.|-)+[\.][^\W\s]{2,4}|localhost(?=\/)|\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})(?::(\d*))?([\/]?[^\s\?]*[\/]{1})*(?:\/?([^\s\n\?\[\]\{\}\#]*(?:(?=\.)){1}|[^\s\n\?\[\]\{\}\.\#]*)?([\.]{1}[^\s\?\#]*)?)?(?:\?{1}([^\s\n\#\[\]]*))?([\#][^\s\n]*)?\)?/";
        preg_match_all($urlRegEx, $value,$matches);

        if (!count($matches[0]))
        {
            return $value;
        }
        else
        {
            $result = $value;
            $protocols = array('//', 'http', 'ftp');
            $doneURLs = array();

            foreach ($matches[0] as $val)
            {
                if (!in_array($val, $doneURLs))
                {
                    $href = $val;
                    $hasProtocol = false;

                    foreach ($protocols as $prot)
                    {
                        if (strpos($href, $prot) === 0) {
                            $hasProtocol = true;
                            break;
                        }
                    }

                    if (!$hasProtocol)
                        $href = 'http://' . $href;

                    $result = str_replace($val, '<a target="_blank" href="' . $href . '">' . $val . '</a>', $result);
                    $doneURLs[] = $val;
                }
            }

            return $result;
        }
    }

    public function blogRole($user)
    {
        if(!$user)
            return "empty";

        return $this->blogUserHandler->getDefaultBlogRoleName($user);
    }
}