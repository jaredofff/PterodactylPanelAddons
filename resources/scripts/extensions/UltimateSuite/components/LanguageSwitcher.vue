<template>
    <div class="language-switcher">
        <label class="block text-sm font-medium text-neutral-300 mb-2">
            {{ t('language_switcher.title') }}
        </label>
        <select 
            v-model="selectedLanguage" 
            @change="changeLanguage"
            class="form-select w-full bg-neutral-800 border-neutral-700 text-neutral-200 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500"
        >
            <option value="en">English</option>
            <option value="es">Español</option>
        </select>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import http from '@/api/http';
// import { useStore } from 'easy-peasy'; // In case you want to fetch it from global state

const { t, locale } = useI18n();
const selectedLanguage = ref(locale.value);

onMounted(() => {
    // You can load current user language from store if available
    // selectedLanguage.value = user.language || 'en';
});

const changeLanguage = async () => {
    try {
        await http.post('/api/client/account/language', {
            language: selectedLanguage.value
        });
        locale.value = selectedLanguage.value;
        // Optional: reload the page to apply fully if using Pterodactyl's native translations as well
    } catch (error) {
        console.error('Failed to change language:', error);
    }
};
</script>
