<?php

namespace Pterodactyl\Extensions\UltimateSuite\Services;

use Pterodactyl\Models\Server;
use Pterodactyl\Services\Servers\VariableValidatorService;
use Pterodactyl\Services\Servers\ReinstallServerService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

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

    public function getAvailableTypes(): array
    {
        return Cache::remember('ultimate_suite_jar_types', 3600, function () {
            $response = Http::get('https://mcjars.app/api/v1/types');
            if ($response->successful()) {
                return $response->json()['types'] ?? [];
            }
            return [];
        });
    }

    public function getVersionsForType(string $type): array
    {
        $type = strtoupper($type);
        return Cache::remember("ultimate_suite_jar_versions_{$type}", 3600, function () use ($type) {
            $response = Http::get("https://mcjars.app/api/v2/builds/{$type}");
            if ($response->successful()) {
                $data = $response->json();
                $builds = $data['builds'] ?? [];
                
                $versions = [];
                foreach ($builds as $version => $info) {
                    $versions[] = [
                        'version' => $version,
                        'is_latest' => false,
                    ];
                }
                
                return array_reverse($versions);
            }
            return [];
        });
    }

    /**
     * Logic to fetch download URL depending on type and version.
     */
    private function getDownloadUrl(string $type, string $version): string
    {
        $type = strtoupper($type);
        $response = Http::get("https://mcjars.app/api/v2/builds/{$type}");
        
        if ($response->successful()) {
            $data = $response->json();
            return $data['builds'][$version]['latest']['jarUrl'] ?? '';
        }

        return '';
    }
}
