<?php

namespace Pterodactyl\Extensions\UltimateSuite\Http\Controllers\Api\Client\Servers;

use Pterodactyl\Models\Server;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Http\Controllers\Api\Client\ClientApiController;
use Pterodactyl\Extensions\UltimateSuite\Http\Requests\ChangeVersionRequest;
use Pterodactyl\Extensions\UltimateSuite\Services\VersionManagerService;

class VersionManagerController extends ClientApiController
{
    private VersionManagerService $versionService;

    public function __construct(VersionManagerService $versionService)
    {
        parent::__construct();
        $this->versionService = $versionService;
    }

    public function getTypes(): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'types' => $this->versionService->getAvailableTypes()
        ]);
    }

    public function getVersions(string $type): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'versions' => $this->versionService->getVersionsForType($type)
        ]);
    }

    public function updateVersion(ChangeVersionRequest $request, Server $server): JsonResponse
    {
        $this->versionService->changeServerVersion($server, $request->input('type'), $request->input('version'));

        return new JsonResponse([
            'success' => true,
            'message' => 'Server version updated and reinstalling in background.'
        ]);
    }
}
