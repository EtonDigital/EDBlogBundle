<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 10.6.15.
 * Time: 08.36
 */

namespace ED\BlogBundle\Forms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ArticlePhotoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('media', 'file',array(
            'attr' => array(
                'class' => 'sr-only media-uploader',
             //   'data-href' => $this->generateUrl('ed_blog_admin_article_upload')
            ),
            'multiple' => true
        ));
    }

    public function getName()
    {
        return "article_media";
    }
}