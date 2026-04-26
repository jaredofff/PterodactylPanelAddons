<template>
    <div class="version-manager bg-neutral-700 p-6 rounded shadow-md border border-neutral-600">
        <h2 class="text-xl font-semibold text-neutral-100 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            {{ t('version_manager.title') }}
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-neutral-300 mb-2">{{ t('version_manager.type') }}</label>
                <div class="relative">
                    <select 
                        v-model="form.type" 
                        @change="fetchVersions"
                        class="form-select w-full bg-neutral-800 border-neutral-600 rounded text-neutral-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        :disabled="isLoadingTypes"
                    >
                        <option v-if="isLoadingTypes" disabled value="">{{ t('version_manager.loading_types') }}</option>
                        <option v-for="(type, key) in types" :key="key" :value="key">
                            {{ type.name }}
                        </option>
                    </select>
                    <div v-if="isLoadingTypes" class="absolute right-3 top-2.5">
                        <svg class="animate-spin h-4 w-4 text-neutral-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-neutral-300 mb-2">{{ t('version_manager.version') }}</label>
                <div class="relative">
                    <select 
                        v-model="form.version" 
                        class="form-select w-full bg-neutral-800 border-neutral-600 rounded text-neutral-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        :disabled="isLoadingVersions || !form.type"
                    >
                        <option value="">{{ t('version_manager.select_version') }}</option>
                        <option v-for="v in versions" :key="v.version" :value="v.version">
                            {{ v.version }}
                        </option>
                    </select>
                    <div v-if="isLoadingVersions" class="absolute right-3 top-2.5">
                        <svg class="animate-spin h-4 w-4 text-neutral-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 flex items-center justify-between">
            <p class="text-xs text-neutral-400 italic">
                {{ t('version_manager.powered_by') }} <a href="https://mcjars.app" target="_blank" class="text-blue-400 hover:underline">mcjars.app</a>
            </p>
            <button 
                @click="updateVersion" 
                :disabled="isUpdating || !form.version"
                class="btn btn-primary bg-blue-600 hover:bg-blue-500 disabled:bg-neutral-600 disabled:cursor-not-allowed text-white px-6 py-2 rounded font-medium transition-all transform active:scale-95 shadow-lg"
            >
                <span v-if="isUpdating" class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ t('version_manager.updating') }}
                </span>
                <span v-else>{{ t('version_manager.update_btn') }}</span>
            </button>
        </div>
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

const isUpdating = ref(false);
const isLoadingTypes = ref(false);
const isLoadingVersions = ref(false);

const types = ref<any>({});
const versions = ref<any[]>([]);

const form = ref({
    type: 'PAPER',
    version: ''
});

const fetchTypes = async () => {
    isLoadingTypes.value = true;
    try {
        const { data } = await http.get(`/api/client/servers/${serverId}/ultimate-suite/version/types`);
        types.value = data.types;
        if (Object.keys(types.value).length > 0) {
            fetchVersions();
        }
    } catch (error) {
        console.error('Failed to fetch types:', error);
    } finally {
        isLoadingTypes.value = false;
    }
};

const fetchVersions = async () => {
    if (!form.value.type) return;
    isLoadingVersions.value = true;
    versions.value = [];
    form.value.version = '';
    
    try {
        const { data } = await http.get(`/api/client/servers/${serverId}/ultimate-suite/version/types/${form.value.type}`);
        versions.value = data.versions;
        if (versions.value.length > 0) {
            form.value.version = versions.value[0].version;
        }
    } catch (error) {
        console.error('Failed to fetch versions:', error);
    } finally {
        isLoadingVersions.value = false;
    }
};

const updateVersion = async () => {
    if (!form.value.version) return;
    isUpdating.value = true;

    try {
        await http.post(`/api/client/servers/${serverId}/ultimate-suite/version`, form.value);
        // Ideally use useFlash here if available in the context
        alert(t('version_manager.success'));
    } catch (error) {
        console.error('Update failed:', error);
    } finally {
        isUpdating.value = false;
    }
};

onMounted(() => {
    fetchTypes();
});
</script>
