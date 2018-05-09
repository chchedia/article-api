<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
//use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\ArticleType;
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
     */
    public function createAction(Request $request)
    {
        $data = $this->get('jms_serializer')->deserialize($request->getContent(), 'array', 'json');
        $article = new Article();
        $form = $this->get('form.factory')->create(ArticleType::class, $article);
        $form->submit($data);

        $em=$this->getDoctrine()->getManager();
        $em->persist($article);
        $em->flush();

        return $this->view($article, Response::HTTP_CREATED,
            ['Location' => $this->generateUrl('app_article_show',
                ['id' => $article->getId(),
                    UrlGeneratorInterface::ABSOLUTE_URL])]);
    }

    /**
     * @Post("/articles/list", name="app_article_list")
     * @RequestParam(
     *     name="search",
     *     default=null,
     *     nullable=true,
     *     description="Search query to look for articles"
     * )
     */
    public function listAction($search)
    {
        die($search);
    }
}