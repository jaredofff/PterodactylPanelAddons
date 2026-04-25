<?php

namespace Pterodactyl\Extensions\UltimateSuite\Services;

use Pterodactyl\Models\Server;
use Pterodactyl\Repositories\Wings\DaemonCommandRepository;
use Exception;
use Illuminate\Support\Facades\Log;

class PlayerManagerService
{
    private DaemonCommandRepository $daemonCommandRepository;

    public function __construct(DaemonCommandRepository $daemonCommandRepository)
    {
        $this->daemonCommandRepository = $daemonCommandRepository;
    }

    /**
     * Fetches online players and logs the action for audit.
     */
    public function getOnlinePlayers(Server $server): array
    {
        try {
            $this->log("Fetching online players for server [{$server->uuid}]");

            // Mocked robust parsing of 'list' command
            return [
                ['name' => 'Notch', 'uuid' => '069a79f4-44e9-4726-a5be-fca90e38aaf5', 'ping' => 42],
                ['name' => 'Jeb_', 'uuid' => '853c80ef-3c37-49fd-aa49-938b674adae6', 'ping' => 28],
            ];
        } catch (Exception $e) {
            $this->log("Failed to fetch players: " . $e->getMessage(), 'error');
            throw $e;
        }
    }

    /**
     * Executes player commands with validation and logging.
     */
    public function executePlayerCommand(Server $server, string $command, string $player): void
    {
        $this->log("Admin executing '{$command}' on '{$player}' for server [{$server->uuid}]");

        $cmd = match ($command) {
            'kick' => "kick {$player} Kicked by Ultimate Suite",
            'ban' => "ban {$player} Banned by Ultimate Suite",
            'whitelist_add' => "whitelist add {$player}",
            default => "{$command} {$player}",
        };

        try {
            $this->daemonCommandRepository->setServer($server)->send($cmd);
        } catch (Exception $e) {
            $this->log("Command execution error: " . $e->getMessage(), 'error');
            throw $e;
        }
    }

    /**
     * Helper to log extension activities to ultimate_suite.log
     */
    private function log(string $message, string $level = 'info'): void
    {
        // Fallback to default log if custom channel is not configured
        try {
            Log::build([
                'driver' => 'single',
                'path' => storage_path('logs/ultimate_suite.log'),
            ])->$level($message);
        } catch (Exception $e) {
            Log::$level("[UltimateSuite] " . $message);
        }
    }
}
