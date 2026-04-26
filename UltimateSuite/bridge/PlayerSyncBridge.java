package com.ultimatesuite.bridge;

import org.bukkit.Bukkit;
import org.bukkit.entity.Player;
import org.bukkit.event.EventHandler;
import org.bukkit.event.Listener;
import org.bukkit.event.player.PlayerJoinEvent;
import org.bukkit.plugin.java.JavaPlugin;

import java.util.UUID;

/**
 * PlayerSyncBridge - Force sync player data between Panel and Server.
 * This is a minimal Spigot/Paper implementation.
 */
public class PlayerSyncBridge extends JavaPlugin implements Listener {

    @Override
    public void onEnable() {
        Bukkit.getPluginManager().registerEvents(this, this);
        getLogger().info("Ultimate Suite Bridge Enabled!");
    }

    /**
     * Triggered via command or internal logic to reload a player
     */
    public void reloadPlayerData(UUID uuid) {
        Player player = Bukkit.getPlayer(uuid);
        if (player != null && player.isOnline()) {
            // Kick player to force the server to read the modified .dat on next join
            player.kickPlayer("§bUltimate Suite: §fActualizando perfil...");
        }
    }
}
