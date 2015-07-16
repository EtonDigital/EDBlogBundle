<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 10.6.15.
 * Time: 08.39
 */

namespace ED\BlogBundle\Forms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ArticleExcerptType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('media', 'file',array(
            'attr' => array(
                'class' => 'sr-only',
//                'data-href' => $this->generateUrl('ed_blog_admin_article_upload')
            ),
            'multiple' => false
        ));
    }

    public function getName()
    {
        return "article_excerpt";
    }


}