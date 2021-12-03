<?php

namespace App\Controller;

use App\Repository\HelpArticleRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Swagger\Annotations as SWG;
use App\Entity\HelpArticle;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use FOS\RestBundle\Controller\Annotations\QueryParam;


/**
 * @Rest\Route("/api/v1/help_articles")
 *
 */
class HelpArticleController extends AbstractFOSRestController
{
    /**
     * @Rest\Route("", name="api_helparticle_list", methods={"GET"})
     * @Rest\View()
     *
     * @QueryParam(name="search", description="Search text")
     *
     * @SWG\Response(response="200", description="List of help articles",
     *     @SWG\Schema(type="array", @SWG\Items(ref=@Model(type=App\Entity\HelpArticle::class)))
     * )
     */
    public function index(HelpArticleRepository $repository, ParamFetcher $paramFetcher)
    {
        $search = $paramFetcher->get('search');

        return $search
            ? $repository->findAllLike($search)
            : $repository->findAll();
    }

    /**
     * @Rest\Route("/{id}", name="api_helparticle_show", requirements={"id": "\d+"}, methods={"GET"})
     * @Rest\Route("/{slug}", name="api_helparticle_show_by_slug", requirements={"slug": "^[a-z\-]+$"}, methods={"GET"})
     * @Rest\View()
     *
     * @SWG\Response(response="200", ref=@Model(type=App\Entity\HelpArticle::class))
     *
     */
    public function show(?HelpArticle $helpArticle, ?string $slug, HelpArticleRepository $repository)
    {
        if ($slug) {
            $helpArticle = $repository->findOneBySlug($slug);
        }

        if (null === $helpArticle) {
            throw new NotFoundHttpException('Help article not found');
        }

        return $helpArticle;
    }

}
