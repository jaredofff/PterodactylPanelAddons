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

    public function __construct(
        ReinstallServerService $reinstallService,
        VariableValidatorService $variableValidatorService
    ) {
        $this->reinstallService = $reinstallService;
        $this->variableValidatorService = $variableValidatorService;
    }

    /**
     * Changes server type and version, updates environment variables, and executes reinstall.
     */
    public function changeServerVersion(Server $server, string $type, string $version): void
    {
        Log::info("Changing version for server {$server->uuid} to {$type} {$version}");

        // 1. Generate dynamic download URL (e.g. PaperMC API)
        $downloadUrl = $this->getDownloadUrl($type, $version);

        // 2. Update server variables
        $variables = [
            'SERVER_TYPE' => $type,
            'MINECRAFT_VERSION' => $version,
            'DOWNLOAD_URL' => $downloadUrl,
            'BUILD_NUMBER' => 'latest'
        ];

        foreach ($variables as $env => $val) {
            $variable = $server->variables()->where('env_variable', $env)->first();
            if ($variable) {
                $this->variableValidatorService->handle($server->id, $variable->id, $val);
            }
        }

        // 3. Reinstall server
        $this->reinstallService->handle($server);
    }

    /**
     * Logic to fetch download URL depending on type and version.
     */
    private function getDownloadUrl(string $type, string $version): string
    {
        if ($type === 'paper') {
            return "https://api.papermc.io/v2/projects/paper/versions/{$version}/builds/latest/downloads/paper-{$version}-latest.jar";
        }

        // Add Logic for Purpur, Fabric, Forge...
        return '';
    }
}
