<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;


class ArticleController extends FOSRestController
{
    /**
     * @Get(
     *     path= "/articles/{id}",
     *     name= "app_article_show",
     *     requirements= {"id"="\d+"}
     *     )
     * @View
     */
    public function showAction(Article $article)
    {
        $data = $this->get('jms_serializer')->serialize($article, 'json');
        $response = new Response($data);
        return $response;
    }
    /**
     * @Post(
     *     path= "/articles",
     *     name= "app_article_create"
     * )
     * @View(StatusCode= 201)
     * @ParamConverter("article", converter="fos_rest.request_body")
     */
    public function createAction(Article $article)
    {
        $em=$this->getDoctrine()->getManager();
        $em->persist($article);
        $em->flush();

        return $this->view($article, Response::HTTP_CREATED,
            ['Location' => $this->generateUrl('app_article_show',
                ['id' => $article->getId(),
                    UrlGeneratorInterface::ABSOLUTE_URL])]);
    }
}