<?php
/**
 * Created by Eton Digital.
 * User: Vladimir Mladenovic (vladimir.mladenovic@etondigital.com)
 * Date: 25.5.15.
 * Time: 14.48
 */

namespace ED\BlogBundle\Forms;

use Doctrine\ORM\EntityRepository;
use ED\BlogBundle\Model\Entity\Article;
use ED\BlogBundle\Transformers\PhotoToIdTransformer;
use ED\BlogBundle\Transformers\TagsToTextTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use ED\BlogBundle\Forms\ArticleMetaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class ArticleType extends AbstractType
{
    protected $dataClass;
    protected $userClass;
    protected $entityManager;
    protected $authorizationChecker;
    protected $taxonomyClass;

    function __construct($dataClass, $userClass, $taxonomyClass, $entityManager, AuthorizationChecker $authorizationChecker)
    {
        $this->dataClass = $dataClass;
        $this->entityManager = $entityManager;
        $this->userClass = $userClass;
        $this->taxonomyClass = $taxonomyClass;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $object = $builder->getData();
        $tagTransformer = new TagsToTextTransformer($this->entityManager->getManager(), $this->taxonomyClass);
        $photoTransformer = new PhotoToIdTransformer($this->entityManager->getManager());

        $builder
            ->add('title', TextType::class,
                array(
                    'required' => true,
                    'label' => 'Title:',
                    'attr' => array(
                        'class' => 'form-control form-control--lg margin--b',
                        'placeholder' => 'Enter title of the article'
                    )
                ))
            ->add('excerpt', TextareaType::class,
                array(
                    'required' => false,
                    'label' => 'Excerpt text:',
                    'attr' => array(
                        'class' => 'form-control form-control--lg margin--halfb',
                        'rows'  => 2,
                        'placeholder' => 'Enter excerpt text'
                    )
                ))
            ->add(
                $builder->create('excerptPhoto', HiddenType::class,
                    array(
                        'attr' => array('class' => 'sr-only js-excerpt-photo'),
                        'required' => false
                    ))->addModelTransformer($photoTransformer)
                )
            ->add('content', TextareaType::class,
                array(
                    'required' => false,
                    'label' => ' ',
                    'attr' => array(
                        'class' => 'tinymce hide'
                    )
                ))
            ->add('categories', EntityType::class, array(
                'class' => $this->taxonomyClass,
                'required' => false,
                'expanded' => true,
                'multiple' => true,
                'attr' => array(
                    'class' => 'js-get-pretty-categories',
                    'placeholder' => 'Select category'
                )))
            ->add(
                $builder->create('tags', TextType::class, array(
                'required' => false,
                'attr' => array(
                    "class" => "form-control form-control--lg margin--halfb",
                    "placeholder" => "Enter tags",
                    "data-role" => "tagsinput"
                )))->addModelTransformer($tagTransformer)
                )
            ->add('metaData', CollectionType::class, array(
                'entry_type' => ArticleMetaType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ))
            ->add('metaExtras', HiddenType::class, array(
                'mapped' => false
            ))
            ;

        if($this->authorizationChecker->isGranted('SWITCH_ARTICLE_AUTHOR'))
        {
            $builder
                ->add('author', EntityType::class, array(
                    'label' => 'Author:',
                    'required' => true,
                    'class' => $this->userClass,
                    'placeholder' => 'Select author',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('a')
                            ->where('a.roles like :type')
                            ->orderBy('a.username', 'ASC')
                            ->setParameter('type', '%ROLE_BLOG_USER%');

                    },
                    'attr' => array(
                        'class' => 'form-control form-control--lg color-placeholder',
                    )
                ));
        }
        if($this->authorizationChecker->isGranted('EDIT_PUBLISH_STATUS', $object))
        {
            $builder
                ->add('status', ChoiceType::class, array(
                    'label' => 'Status:',
                    'choices' => array(
                        "Published" => Article::STATUS_PUBLISHED,
                        "Draft" => Article::STATUS_DRAFTED
                    ),
                    'required' => true,
                    'attr' => array(
                        "class" => "form-control form-control--lg margin--halfb",
                    ),
                    'data' => $object->getParent() ? $object->getParent()->getStatus() : Article::STATUS_DRAFTED
                ));
        }


        if(!$object->getParent())
        {
            //When creating new articles
            if($this->authorizationChecker->isGranted('PUBLISH_ARTICLE', $object))
            {
                $builder
                    ->add('publish', SubmitType::class,
                        array(
                            'attr' => array('class' => 'btn btn-md btn-primary btn-wide--xl flright--responsive-mob margin--b first-in-line js-publish-article')
                        ));
            }

            $builder->add('save_draft', SubmitType::class,
                    array(
                        'attr' => array('class' => 'btn btn-md btn-b-blue btn-wide--xl flright--responsive-mob margin--r')
                    ));
        }
        else
        {
            if($object && $object->getParent() && $object->getParent()->getStatus() == Article::STATUS_DRAFTED)
            {
                $builder
                    ->add('save', SubmitType::class,
                        array(
                            'attr' => array('class' => 'btn btn-md btn-b-blue btn-wide--xl flright--responsive-mob margin--r')
                        ));

                if($this->authorizationChecker->isGranted('PUBLISH_ARTICLE', $object))
                {
                    $builder
                        ->add('publish', SubmitType::class,
                            array(
                                'attr' => array('class' => 'btn btn-md btn-primary btn-wide--xl flright--responsive-mob margin--b first-in-line js-publish-article')
                            ));
                }
            }
            else
            {
                $builder->add('update', SubmitType::class,
                    array(
                        'attr' => array('class' => 'btn btn-md btn-primary btn-wide--xl flright--responsive-mob margin--r')
                    ));
            }
        }


    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getBlockPrefix()
    {
        return "article";
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->dataClass,
        ));
    }


}