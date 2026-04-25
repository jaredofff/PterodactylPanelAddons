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

    /**
     * Retrieve list of online players.
     */
    public function getPlayers(Server $server): JsonResponse
    {
        $players = $this->playerService->getOnlinePlayers($server);
        return new JsonResponse(['data' => $players]);
    }

    /**
     * Execute commands such as Kick, Ban, Whitelist or custom ones.
     */
    public function executeCommand(ExecuteCommandRequest $request, Server $server): JsonResponse
    {
        $command = $request->input('command');
        $player = $request->input('player');
        
        $this->playerService->executePlayerCommand($server, $command, $player);

        return new JsonResponse([
            'success' => true,
            'message' => 'Command executed successfully.'
        ]);
    }
}
