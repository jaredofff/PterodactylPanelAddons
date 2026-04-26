<?php

namespace Pterodactyl\Extensions\UltimateSuite\Services;

use Exception;
use Pterodactyl\Models\Server;
use fNBT\NBT;
use fNBT\Tag\Compound;
use fNBT\Tag\IntTag;
use fNBT\Tag\DoubleTag;
use fNBT\Tag\ListTag;
use Illuminate\Support\Facades\Log;

/**
 * PlayerNbtService - Manages Minecraft Player Data (.dat) via NBT.
 * Requires frazz/nbt or similar library.
 */
class PlayerNbtService
{
    private string $playerDataPath = '/world/playerdata/';

    /**
     * Reads a player's .dat file and returns a structured array for the UI.
     */
    public function getPlayerData(Server $server, string $uuid): array
    {
        $filePath = $this->getServerFilePath($server, $uuid);

        if (!file_exists($filePath)) {
            throw new Exception("Player data file not found: {$filePath}");
        }

        try {
            $nbt = new NBT();
            $nbt->loadFile($filePath, true); // true for gzipped
            $root = $nbt->getRoot();

            return [
                'uuid' => $uuid,
                'health' => $root['Health']->getValue(),
                'food' => $root['foodLevel']->getValue(),
                'xp_level' => $root['XpLevel']->getValue(),
                'pos' => [
                    'x' => $root['Pos'][0]->getValue(),
                    'y' => $root['Pos'][1]->getValue(),
                    'z' => $root['Pos'][2]->getValue(),
                ],
                'rotation' => [
                    'yaw' => $root['Rotation'][0]->getValue(),
                    'pitch' => $root['Rotation'][1]->getValue(),
                ],
                'inventory' => $this->parseInventory($root['Inventory']),
            ];
        } catch (Exception $e) {
            Log::error("[UltimateSuite] Error reading NBT for {$uuid}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Writes updated data back to the .dat file.
     */
    public function savePlayerData(Server $server, string $uuid, array $data): void
    {
        // 1. Check server status for safety
        $this->ensureServerSafeToWrite($server);

        $filePath = $this->getServerFilePath($server, $uuid);
        
        try {
            $nbt = new NBT();
            $nbt->loadFile($filePath, true);
            $root = $nbt->getRoot();

            // Update Basic Stats
            if (isset($data['health'])) $root['Health']->setValue((float)$data['health']);
            if (isset($data['xp_level'])) $root['XpLevel']->setValue((int)$data['xp_level']);

            // Update Position
            if (isset($data['pos'])) {
                $root['Pos'][0]->setValue((double)$data['pos']['x']);
                $root['Pos'][1]->setValue((double)$data['pos']['y']);
                $root['Pos'][2]->setValue((double)$data['pos']['z']);
            }

            // Update Inventory
            if (isset($data['inventory'])) {
                $this->updateInventory($root, $data['inventory']);
            }

            $nbt->saveFile($filePath, true);
            Log::info("[UltimateSuite] Player data saved for {$uuid}");
        } catch (Exception $e) {
            Log::error("[UltimateSuite] Failed to save NBT for {$uuid}: " . $e->getMessage());
            throw $e;
        }
    }

    private function parseInventory($inventoryTag): array
    {
        $items = [];
        foreach ($inventoryTag as $tag) {
            $items[] = [
                'id' => $tag['id']->getValue(),
                'count' => $tag['Count']->getValue(),
                'slot' => $tag['Slot']->getValue(),
                'damage' => $tag['Damage'] ? $tag['Damage']->getValue() : 0,
            ];
        }
        return $items;
    }

    private function updateInventory(Compound $root, array $newInventory): void
    {
        // This is a simplified replacement logic
        $list = new ListTag('Inventory', Compound::class);
        foreach ($newInventory as $item) {
            $compound = new Compound();
            $compound['id'] = $item['id'];
            $compound['Count'] = (int)$item['count'];
            $compound['Slot'] = (int)$item['slot'];
            $list->push($compound);
        }
        $root['Inventory'] = $list;
    }

    private function getServerFilePath(Server $server, string $uuid): string
    {
        // IMPORTANT: In Pterodactyl, this assumes the Panel has disk access to /var/lib/pterodactyl/volumes
        // Adjust this path according to your environment
        return "/var/lib/pterodactyl/volumes/{$server->uuid}/world/playerdata/{$uuid}.dat";
    }

    private function ensureServerSafeToWrite(Server $server): void
    {
        // In a real scenario, check if server is 'offline' via Pterodactyl API or Wings status
        // If online, you MUST send 'save-off' via RCON/Wings before writing.
        if ($server->status !== 'offline') {
            // Log::warning("Writing to player data while server is online is risky!");
            // Implementation: Send "save-off" command here
        }
    }
}
