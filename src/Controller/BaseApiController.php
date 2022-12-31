<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\AccountService;
use App\Service\Converter\ApiConverter;
use App\Service\MediaService;
use App\Service\StatusService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/** @SuppressWarnings(PHPMD.NumberOfChildren) */
class BaseApiController extends AbstractController
{
    use AccountTrait;

    protected AccountService $accountService;
    protected StatusService $statusService;
    protected MediaService $mediaService;
    protected ApiConverter $apiModelConverter;
    protected LoggerInterface $logger;

    public function __construct(
        AccountService $accountService,
        StatusService $statusService,
        MediaService $mediaService,
        ApiConverter $apiModelConverter,
        LoggerInterface $logger
    ) {
        $this->accountService = $accountService;
        $this->statusService = $statusService;
        $this->mediaService = $mediaService;
        $this->apiModelConverter = $apiModelConverter;
        $this->logger = $logger;
    }
}
