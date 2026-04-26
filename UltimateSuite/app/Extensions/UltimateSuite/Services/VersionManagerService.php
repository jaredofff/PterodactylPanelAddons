<?php

namespace Pterodactyl\Extensions\UltimateSuite\Services;

use Pterodactyl\Models\Server;
use Pterodactyl\Services\Servers\VariableValidatorService;
use Pterodactyl\Services\Servers\ReinstallServerService;
use Illuminate\Support\Facades\Log;

class VersionManagerService
{
    private ReinstallServerService $reinstallService;
    private VariableValidatorService $variableValidatorService;

    public function __construct(ReinstallServerService $reinstallService, VariableValidatorService $variableValidatorService)
    {
        $this->reinstallService = $reinstallService;
        $this->variableValidatorService = $variableValidatorService;
    }

    public function changeServerVersion(Server $server, string $type, string $version): void
    {
        $this->log("Changing version for server [{$server->uuid}] to {$type} {$version}");
        
        $downloadUrl = $this->getDownloadUrl($type, $version);
        
        // Find suitable variables automatically
        $versionVar = $server->variables()->where('env_variable', 'like', '%VERSION%')->first();
        $typeVar = $server->variables()->where('env_variable', 'like', '%TYPE%')->first();
        $urlVar = $server->variables()->where('env_variable', 'like', '%URL%')->first();

        if ($versionVar) $this->variableValidatorService->handle($server->id, $versionVar->id, $version);
        if ($typeVar) $this->variableValidatorService->handle($server->id, $typeVar->id, $type);
        if ($urlVar && $downloadUrl) $this->variableValidatorService->handle($server->id, $urlVar->id, $downloadUrl);

        // Trigger Reinstall
        $this->reinstallService->handle($server);
        
        $this->log("Reinstallation triggered for server [{$server->uuid}]");
    }

    private function log(string $message, string $level = 'info'): void
    {
        Log::info("[UltimateSuite] " . $message);
    }

    private function getDownloadUrl(string $type, string $version): string
    {
        if ($type === 'paper') return "https://api.papermc.io/v2/projects/paper/versions/{$version}/builds/latest/downloads/paper-{$version}-latest.jar";
        return '';
    }
}
