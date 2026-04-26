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
                        'is_latest' => false, // We could determine this if needed
                    ];
                }
                
                return array_reverse($versions); // Newest first
            }
            return [];
        });
    }

    private function log(string $message, string $level = 'info'): void
    {
        Log::info("[UltimateSuite] " . $message);
    }

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
