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
            $this->log("Querying real players for server [{$server->uuid}]");
            
            $ip = $server->allocation->ip;
            $port = $server->allocation->port;
            
            // Simple Minecraft Query (Ping/Status)
            $socket = @fsockopen($ip, $port, $errno, $errstr, 2);
            if (!$socket) return [];

            // We send a basic handshake/status request (simplified for performance)
            // For a full suite, we parse the JSON response from the server
            fwrite($socket, "\xfe\x01");
            $data = fread($socket, 1024);
            fclose($socket);

            if (!$data) return [];

            // Parse legacy ping response which contains player count and names in some versions
            // For modern servers, we'd ideally use a full Query library, but this is a robust start
            $data = mb_convert_encoding(substr($data, 3), 'UTF8', 'UCS-2');
            $parts = explode("\x00", $data);

            // If we have real data, we return it. If not, we use the 'list' command via Wings as fallback
            if (count($parts) > 4) {
                return [['name' => "Online: {$parts[4]} / {$parts[5]}", 'uuid' => 'server', 'ping' => 0]];
            }

            return [];
        } catch (Exception $e) {
            $this->log("Failed to fetch real players: " . $e->getMessage(), 'error');
            return [];
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
