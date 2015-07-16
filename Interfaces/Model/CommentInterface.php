<?php
/**
 * Created by Eton Digital.
 * User: Milos Milojevic (milos.milojevic@etondigital.com)
 * Date: 6/2/15
 * Time: 12:04 PM
 */

namespace ED\BlogBundle\Interfaces\Model;


interface CommentInterface
{
    public function setComment($comment);

    public function getComment();

    public function setName($name);

    public function getName();

    public function setArticle($article);

    public function getArticle();

    public function setParent(CommentInterface $comment);

    public function getParent();

    public function setStatus($status);

    public function getStatus();

    public function setEmail($email);

    public function getEmail();
}