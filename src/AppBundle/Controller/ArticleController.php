<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Article;
//use FOS\RestBundle\Controller\FOSRestController;
use AppBundle\Exception\ResourceValidationException;
use AppBundle\Representation\Articles;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationList;
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
     * @ParamConverter("article",
     *     converter="fos_rest.request_body",
     *     options= { "validator"= {"groups"="Create"}}
     *     )
     */
    public function createAction(Article $article, Request $request, ConstraintViolationList $violations)
    {
        if (count($violations)) {
            $message = 'The JSON sent contains invalid data:';
            foreach ($violations as $violation) {
                $message .= sprintf(
                    "Field %s: %s",
                    $violation->getPropertyPath(),
                    $violation->getMessage()
                );
            }
            throw new ResourceValidationException($message);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($article);
        $em->flush();

        return $this->view($article, Response::HTTP_CREATED,
            ['Location' => $this->generateUrl('app_article_show',
                ['id' => $article->getId(),
                    UrlGeneratorInterface::ABSOLUTE_URL])]);
    }

    /**
     * @Get("/articles/list", name="app_article_list")
     * @QueryParam(
     *     name="keyword",
     *     requirements="[a-zA-Z0-9]",
     *     nullable=true,
     *     description="the keyword to search for"
     * )
     * @QueryParam(
     *     name="order",
     *     requirements="asc|desc",
     *     default="asc",
     *     description="sort order (asc or desc)"
     * )
     * @QueryParam(
     *     name="limit",
     *     requirements="\d+",
     *     default="15",
     *     description="Max number of articles per page"
     * )
     * @QueryParam(
     *     name="offset",
     *     requirements="\d+",
     *     default="0",
     *     description="the pagination offset"
     * )
     */
    public function listAction(ParamFetcherInterface $paramFetcher)
    {
        $pager = $this->getDoctrine()->getRepository('AppBundle:Article')->search(
            $paramFetcher->get('keyword'),
            $paramFetcher->get('order'),
            $paramFetcher->get('limit'),
            $paramFetcher->get('offset')
        );

        return $this->view(new Articles($pager), Response::HTTP_OK);
    }

    /**
     * @Put(
     *     path= "/articles/{id}",
     *     name= "app_article_update"
     * )
     * @View(StatusCode= 200)
     * @ParamConverter("article",converter="fos_rest.request_body")
     */
    public function updateAction (Article $article,Request $request,ConstraintViolationList $violations )
    {
        if (count($violations)) {
            $message = 'The JSON sent contains invalid data:';
            foreach ($violations as $violation) {
                $message .= sprintf(
                    "Field %s: %s",
                    $violation->getPropertyPath(),
                    $violation->getMessage()
                );
            }
            throw new ResourceValidationException($message);
        }
        $em= $this->getDoctrine()->getManager();
        $oldArticle= $em->getRepository('AppBundle:Article')->findOneById($request->get('id'));
        if (empty($oldArticle)) {
            return $this->view(new JsonResponse(['message' => 'Article not found'], Response::HTTP_NOT_FOUND));
        } else {
            $oldArticle->setContent($article->getContent());
            $oldArticle->setTitle($article->getTitle());
            $em->flush();
            return $this->view($oldArticle, Response::HTTP_OK);
        }
    }

    /**
     * @Delete(
     *     path= "/articles/{id}",
     *     name= "app_article_delete"
     * )
     * @View(StatusCode= 200)
     */
    public function deleteAction (Request $request)
    {
        $em= $this->getDoctrine()->getManager();
        $article= $em->getRepository('AppBundle:Article')->findOneById($request->get('id'));
        if (empty($article)) {
            return $this->view(new JsonResponse(['message' => 'Article not found'], Response::HTTP_NOT_FOUND));
        } else {
            $em->remove($article);
            $em->flush();
            return $this->view("Article supprimé !");
        }
    }
}