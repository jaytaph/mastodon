<?php

declare(strict_types=1);

namespace App\Controller\Web;

use App\Controller\AccountTrait;
use App\Service\AccountService;
use App\Service\Converter\ActivityStreamConverter;
use App\Service\StatusService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

class StatusController extends AbstractController
{
    use AccountTrait;

    protected AccountService $accountService;
    protected StatusService $statusService;
    protected ActivityStreamConverter $activityStreamConverter;

    public function __construct(AccountService $accountService, StatusService $statusService, ActivityStreamConverter $activityStreamConverter)
    {
        $this->accountService = $accountService;
        $this->statusService = $statusService;
        $this->activityStreamConverter = $activityStreamConverter;
    }

    #[Cache(vary: ['Accept'])]
    #[Route('/users/{acct}/status/{id}', name: 'app_status_show')]
    public function user(Request $request, string $acct, string $id): Response
    {
        $account = $this->findAccount($acct, localOnly: true);
        if (!$account) {
            throw $this->createNotFoundException();
        }

        $status = $this->statusService->findStatusById(Uuid::fromString($id));
        if (!$status) {
            throw $this->createNotFoundException();
        }

        if ($status->getAccount()?->getId() !== $account->getId()) {
            throw $this->createNotFoundException();
        }

        // @TODO: Do we need to check for private statuses??

        if ($request->getPreferredFormat() == 'json') {
            $data = $this->activityStreamConverter->status($status);
            return new JsonResponse($data);
        }

        return $this->render('status/show.html.twig', [
            'status' => $status,
        ]);
    }
}
