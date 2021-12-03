<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;

/**
 * @Rest\Route("/api")
 */
class ApiSecurityController extends AbstractFOSRestController
{

    /**
     * Login
     *
     * @Rest\Route("/login_check", name="api_login_check", methods={"POST"})
     * @Rest\View()
     *
     * @SWG\Parameter(name="Payload", description="User credentials", required=true, in="body", required=true, @SWG\Schema(
     *     type="object",
     *     example={"username": "someuser@test.com", "password": "somepassword"}
     * ))
     * @SWG\Response(response=200, description="Returns the Bearer auth token",
     *     @SWG\Schema(
     *         type="object",
     *         example={"token": "<token>"}
     *     )
     * )
     */
    public function loginCheck()
    {

    }
}
