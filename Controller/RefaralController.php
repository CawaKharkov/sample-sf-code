<?php

namespace App\Controller;

use App\Entity\Referal;
use App\Form\ReferalType;
use App\Referal\ReferalService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * @Rest\Route("/api/v1/referal")
 */
class RefaralController extends AbstractFOSRestController
{
    /**
     * Creates new referal
     *
     * @Route("", name="referal_create", methods={"POST"})
     * @Rest\View()
     */
    public function create(Request $request, LoggerInterface $logger, ReferalService $referalService)
    {
        $logger->info("Incoming referal request: " . $request->getContent());

        $referal = new Referal();

        $form = $this->createForm(ReferalType::class, $referal);

        $form->submit($request->request->all(), false);

        if ($form->isSubmitted() && $form->isValid()) {
            $link = $referalService->create($referal);

            return ['link' => $link];
        }

        return $form;
    }

}
