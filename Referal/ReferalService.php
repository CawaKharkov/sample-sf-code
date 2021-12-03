<?php

namespace App\Referal;

use App\Entity\Referal;
use Doctrine\ORM\EntityManagerInterface;

class ReferalService
{
    /**
     * @var string
     */
    private $frontendUrl;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * ReferalService constructor.
     * @param string $frontendUrl
     * @param EntityManagerInterface $em
     */
    public function __construct(string $frontendUrl, EntityManagerInterface $em)
    {
        $this->frontendUrl = $frontendUrl;
        $this->em = $em;
    }

    /**
     * @param Referal $refaral
     * @return string Link to register using referal hash
     */
    public function create(Referal $refaral): string
    {
        $hash = md5(uniqid()) . uniqid();
        $refaral->setHash($hash);

        $this->em->persist($refaral);
        $this->em->flush();

        return $this->frontendUrl . '/sign-up?hash=' . $hash;
    }

}