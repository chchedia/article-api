<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\View;


class ArticleController
{
    /**
     * @Get(
     *     path= "/articles/{id}",
     *     name= "app_article_show",
     *     requirements= {"id"="\d+"}
     *     )
     * @View
     */
    public function showAction()
    {
        $article = new Article();
        $article->setTitle("hola !");
        $article->setContent("bOnjou!! je suis là!");
        return $article;
    }
}