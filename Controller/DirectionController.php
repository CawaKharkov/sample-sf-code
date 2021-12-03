<?php

namespace App\Controller;

use App\Repository\DirectionRepository;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;


/**
 * @Rest\Route("/api/v1/directions")
 */
class DirectionController extends AbstractFOSRestController
{

}
