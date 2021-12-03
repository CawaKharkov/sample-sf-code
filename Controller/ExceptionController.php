<?php

namespace App\Controller;

use App\Exception\InvalidParameterMessageConverter;
use FOS\RestBundle\Controller\ExceptionController as BaseExceptionController;
use FOS\RestBundle\Exception\InvalidParameterException;
use FOS\RestBundle\Util\ExceptionValueMap;
use FOS\RestBundle\View\ViewHandlerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;

class ExceptionController extends BaseExceptionController
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(ViewHandlerInterface $viewHandler, ExceptionValueMap $exceptionCodes, $showException, LoggerInterface $logger)
    {
        $this->logger = $logger;
        parent::__construct($viewHandler, $exceptionCodes, $showException);
    }

    /**
     * {@inheritDoc}
     */
    public function showAction(Request $request, $exception, DebugLoggerInterface $logger = null)
    {
        if ($exception instanceof InvalidParameterException) {
            $converter = new InvalidParameterMessageConverter();
            $message = $converter->getMessage($exception);
            $this->logger->warning($exception->getMessage(), ['e' => $exception->getTraceAsString()]);
            $exception = new InvalidParameterException($message, $exception);
        }

        if ($exception instanceof BadRequestHttpException) {
            $this->logger->warning($exception->getMessage(), ['e' => $exception->getTraceAsString()]);
        } else {
            $this->logger->critical($exception->getMessage(), ['e' => $exception->getTraceAsString()]);
        }

        return parent::showAction($request, $exception, $logger);
    }
}
