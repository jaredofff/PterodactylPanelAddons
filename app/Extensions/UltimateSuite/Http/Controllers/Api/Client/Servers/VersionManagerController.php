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

    /**
     * Updates server version and triggers a reinstall.
     */
    public function updateVersion(ChangeVersionRequest $request, Server $server): JsonResponse
    {
        $type = $request->input('type');
        $version = $request->input('version');

        $this->versionService->changeServerVersion($server, $type, $version);

        return new JsonResponse([
            'success' => true,
            'message' => 'Version updating and server reinstalling in background.'
        ]);
    }
}
