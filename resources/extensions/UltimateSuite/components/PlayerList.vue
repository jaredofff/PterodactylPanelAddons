<template>
    <div class="player-manager bg-neutral-700 p-6 rounded shadow-md mt-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-neutral-100">{{ t('player_manager.title') }}</h2>
            <button @click="fetchPlayers" class="text-blue-400 hover:text-blue-300">
                {{ t('player_manager.refresh') }}
            </button>
        </div>

        <div v-if="loading" class="text-neutral-400">{{ t('player_manager.loading') }}</div>
        
        <table v-else class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-neutral-600 text-neutral-300">
                    <th class="py-2">Player</th>
                    <th class="py-2">Ping</th>
                    <th class="py-2 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="player in players" :key="player.uuid" class="border-b border-neutral-600/50">
                    <td class="py-3 flex items-center">
                        <img :src="`https://crafatar.com/avatars/${player.uuid}?size=32`" class="w-8 h-8 rounded mr-3" />
                        {{ player.name }}
                    </td>
                    <td class="py-3 text-neutral-400">{{ player.ping }}ms</td>
                    <td class="py-3 text-right space-x-2">
                        <button @click="executeAction('kick', player.name)" class="text-xs bg-yellow-600 hover:bg-yellow-500 px-2 py-1 rounded text-white">Kick</button>
                        <button @click="executeAction('ban', player.name)" class="text-xs bg-red-600 hover:bg-red-500 px-2 py-1 rounded text-white">Ban</button>
                    </td>
                </tr>
                <tr v-if="players.length === 0">
                    <td colspan="3" class="py-4 text-center text-neutral-400">{{ t('player_manager.no_players') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import http from '@/api/http';
import { useRoute } from 'vue-router';

const { t } = useI18n();
const route = useRoute();
const serverId = route.params.id;

const players = ref([]);
const loading = ref(true);

const fetchPlayers = async () => {
    loading.value = true;
    try {
        const { data } = await http.get(`/api/client/servers/${serverId}/ultimate-suite/players`);
        players.value = data.data;
    } catch (error) {
        console.error('Failed to fetch players:', error);
    } finally {
        loading.value = false;
    }
};

const executeAction = async (command: string, playerName: string) => {
    try {
        await http.post(`/api/client/servers/${serverId}/ultimate-suite/players/command`, {
            command: command,
            player: playerName
        });
        alert(`${command} executed for ${playerName}`);
        fetchPlayers();
    } catch (error) {
        console.error('Action failed:', error);
    }
};

onMounted(() => {
    fetchPlayers();
    // Optional polling:
    // setInterval(fetchPlayers, 10000);
});
</script>
