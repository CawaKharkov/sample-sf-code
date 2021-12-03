<?php

namespace App\Controller;

use App\Repository\SecretQuestionRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;


/**
 * @Rest\Route("/api/v1/secret_questions")
 */
class SecretQuestionController extends AbstractFOSRestController
{
    /**
     * Secret questions
     *
     * @Rest\Route("", name="api_secretquestion_list", methods={"GET"})
     * @Rest\View(serializerGroups={"list"})
     *
     * @SWG\Response(response="200", description="List of secret questions",
     *     @SWG\Schema(type="array", @SWG\Items(ref=@Model(type=App\Entity\SecretQuestion::class, groups={"list"})))
     * )
     *
     */
    public function index(SecretQuestionRepository $repository)
    {
        return $repository->findAll();
    }
}
