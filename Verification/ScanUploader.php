<?php

namespace App\Verification;


use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ScanUploader
{
    const IMAGE_DOCUMENT = 'document';
    const IMAGE_SELFIE = 'selfie';

    private $userScanSetters = [
        self::IMAGE_DOCUMENT     => 'setImageDocument',
        self::IMAGE_SELFIE  => 'setImageSelfie',
    ];

    /**
     * @var string
     */
    private $uploadWebDir;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * ScanUploader constructor.
     * @param string $uploadWebDir
     */
    public function __construct(string $uploadWebDir, EntityManagerInterface $entityManager)
    {
        $this->uploadWebDir = $uploadWebDir;
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $type
     * @param UploadedFile $scan
     * @param User $user
     * @throws BadRequestHttpException
     */
    public function upload(string $type, UploadedFile $scan, User $user)
    {
        if (!array_key_exists($type, $this->userScanSetters)) {
            throw new BadRequestHttpException("Unknown user scan type: '$type'");
        }

        $filename = $type . '.' . $scan->guessClientExtension();

        $dir = $this->uploadWebDir . '/' . $user->getId();
        $scan->move($dir, $filename);
        $user->{$this->userScanSetters[$type]}($dir . '/' . $filename);

        $this->entityManager->flush();
    }

}