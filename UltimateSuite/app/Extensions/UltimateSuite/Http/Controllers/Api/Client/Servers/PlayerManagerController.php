<?php

namespace Pterodactyl\Extensions\UltimateSuite\Http\Controllers\Api\Client\Servers;

use Pterodactyl\Models\Server;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Http\Controllers\Api\Client\ClientApiController;
use Pterodactyl\Extensions\UltimateSuite\Http\Requests\ExecuteCommandRequest;
use Pterodactyl\Extensions\UltimateSuite\Services\PlayerManagerService;
use Pterodactyl\Extensions\UltimateSuite\Services\PlayerNbtService;
use Illuminate\Http\Request;

class PlayerManagerController extends ClientApiController
{
    private PlayerManagerService $playerService;
    private PlayerNbtService $nbtService;

    public function __construct(PlayerManagerService $playerService, PlayerNbtService $nbtService)
    {
        parent::__construct();
        $this->playerService = $playerService;
        $this->nbtService = $nbtService;
    }

    public function getPlayers(Server $server): JsonResponse
    {
        return new JsonResponse(['data' => $this->playerService->getOnlinePlayers($server)]);
    }

    public function getPlayerNbt(Server $server, string $uuid): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'data' => $this->nbtService->getPlayerData($server, $uuid)
        ]);
    }

    public function savePlayerNbt(Request $request, Server $server, string $uuid): JsonResponse
    {
        $this->nbtService->savePlayerData($server, $uuid, $request->all());
        return new JsonResponse(['success' => true]);
    }

    public function executeCommand(ExecuteCommandRequest $request, Server $server): JsonResponse
    {
        $this->playerService->executePlayerCommand($server, $request->input('command'), $request->input('player'));
        return new JsonResponse(['success' => true]);
    }
}
