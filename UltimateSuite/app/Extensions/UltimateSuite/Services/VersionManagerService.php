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
        $downloadUrl = $this->getDownloadUrl($type, $version);
        $variables = ['SERVER_TYPE' => $type, 'MINECRAFT_VERSION' => $version, 'DOWNLOAD_URL' => $downloadUrl];

        foreach ($variables as $env => $val) {
            $variable = $server->variables()->where('env_variable', $env)->first();
            if ($variable) {
                $this->variableValidatorService->handle($server->id, $variable->id, $val);
            }
        }
        $this->reinstallService->handle($server);
    }

    private function getDownloadUrl(string $type, string $version): string
    {
        if ($type === 'paper') return "https://api.papermc.io/v2/projects/paper/versions/{$version}/builds/latest/downloads/paper-{$version}-latest.jar";
        return '';
    }
}
