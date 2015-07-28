<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 22.5.15.
 * Time: 10.14
 */

namespace ED\BlogBundle\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use ED\BlogBundle\Interfaces\Model\ArticleInterface;
use ED\BlogBundle\Interfaces\Model\BlogTaxonomyInterface;
use ED\BlogBundle\Interfaces\Model\TaxonomyRelationInterface;


class TaxonomyRelation implements TaxonomyRelationInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="ED\BlogBundle\Interfaces\Model\ArticleInterface", inversedBy="taxonomyRelations")
     * @ORM\JoinColumn(name="article_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $article;

    /**
     * @ORM\ManyToOne(targetEntity="ED\BlogBundle\Interfaces\Model\BlogTaxonomyInterface")
     * @ORM\JoinColumn(name="taxonomy_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $taxonomy;

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
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * @param mixed $article
     */
    public function setArticle(ArticleInterface $article)
    {
        $this->article = $article;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTaxonomy()
    {
        return $this->taxonomy;
    }

    /**
     * @param mixed $taxonomy
     */
    public function setTaxonomy(BlogTaxonomyInterface $taxonomy)
    {
        $this->taxonomy = $taxonomy;
        return $this;
    }


}