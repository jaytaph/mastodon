<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\BaseApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

class MediaController extends BaseApiController
{
    #[Route('/api/v1/media', name: 'api_media_upload', methods: 'POST')]
    #[IsGranted('ROLE_OAUTH2_WRITE')]
    public function mediaUpload(Request $request): Response
    {
        /** @var UploadedFile $file */
        $file = $request->files->get('file');
        $mediaAttachment = $this->mediaService->createMediaAttachment($file);

        return new JsonResponse($mediaAttachment->toArray());
    }

    #[Route('/api/v1/media/{uuid}', name: 'api_media_edit', methods: 'PUT')]
    #[IsGranted('ROLE_OAUTH2_WRITE')]
    public function mediaEdit(Request $request, string $uuid): Response
    {
        $mediaAttachment = $this->mediaService->findMediaAttachmentById(Uuid::fromString($uuid));
        if (!$mediaAttachment) {
            throw $this->createNotFoundException();
        }

        if ($request->request->has('description')) {
            $mediaAttachment->setDescription(strval($request->get('description')));
        }
        if ($request->request->has('focus')) {
            $focus = $request->get('focus');
            if (!is_array($focus)) {
                $focus = [$focus];
            }
            $mediaAttachment->setFocus($focus);
        }
        if ($request->request->has('blurhash')) {
            $mediaAttachment->setBlurhash(strval($request->get('blurhash')));
        }
        if ($request->request->has('meta')) {
            $meta = $request->get('meta');
            if (!is_array($meta)) {
                $meta = [$meta];
            }
            $mediaAttachment->setMeta($meta);
        }
        if ($request->request->has('remote_url')) {
            $mediaAttachment->setRemoteUrl(strval($request->get('remote_url')));
        }

        $this->mediaService->save($mediaAttachment);

        return new JsonResponse($mediaAttachment->toArray());
    }
}
