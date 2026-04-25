<template>
    <div class="version-manager bg-neutral-700 p-6 rounded shadow-md">
        <h2 class="text-xl font-semibold text-neutral-100 mb-4">{{ t('version_manager.title') }}</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-neutral-300 mb-2">{{ t('version_manager.type') }}</label>
                <select v-model="form.type" class="form-select w-full bg-neutral-800 border-neutral-600 rounded">
                    <option value="paper">Paper</option>
                    <option value="purpur">Purpur</option>
                    <option value="fabric">Fabric</option>
                    <option value="forge">Forge</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-neutral-300 mb-2">{{ t('version_manager.version') }}</label>
                <input v-model="form.version" type="text" placeholder="1.20.4" class="form-input w-full bg-neutral-800 border-neutral-600 rounded" />
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <button 
                @click="updateVersion" 
                :disabled="isUpdating"
                class="btn btn-primary bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded transition-colors"
            >
                {{ isUpdating ? t('version_manager.updating') : t('version_manager.update_btn') }}
            </button>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import http from '@/api/http';
import { useRoute } from 'vue-router';

const { t } = useI18n();
const route = useRoute();
const serverId = route.params.id; // Pterodactyl uses short UUID in route params

const isUpdating = ref(false);
const form = ref({
    type: 'paper',
    version: ''
});

const updateVersion = async () => {
    if (!form.value.version) return;
    isUpdating.value = true;

    try {
        await http.post(`/api/client/servers/${serverId}/ultimate-suite/version`, form.value);
        alert(t('version_manager.success')); // In Pterodactyl, preferably use the `useFlash` helper
    } catch (error) {
        console.error('Update failed:', error);
    } finally {
        isUpdating.value = false;
    }
};
</script>
