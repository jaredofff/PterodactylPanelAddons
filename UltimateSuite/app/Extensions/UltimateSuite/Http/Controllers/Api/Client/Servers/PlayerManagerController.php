<?php

namespace Pterodactyl\Extensions\UltimateSuite\Http\Controllers\Api\Client\Servers;

use Pterodactyl\Models\Server;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Http\Controllers\Api\Client\ClientApiController;
use Pterodactyl\Extensions\UltimateSuite\Http\Requests\ExecuteCommandRequest;
use Pterodactyl\Extensions\UltimateSuite\Services\PlayerManagerService;

class PlayerManagerController extends ClientApiController
{
    private PlayerManagerService $playerService;

    public function __construct(PlayerManagerService $playerService)
    {
        parent::__construct();
        $this->playerService = $playerService;
    }

    public function getPlayers(Server $server): JsonResponse
    {
        return new JsonResponse(['data' => $this->playerService->getOnlinePlayers($server)]);
    }

    public function executeCommand(ExecuteCommandRequest $request, Server $server): JsonResponse
    {
        $this->playerService->executePlayerCommand($server, $request->input('command'), $request->input('player'));
        return new JsonResponse(['success' => true]);
    }
}
