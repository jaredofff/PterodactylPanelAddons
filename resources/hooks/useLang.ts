import { useTranslation } from 'react-i18next';

/**
 * Custom hook for language management within the Ultimate Suite.
 */
export const useLang = () => {
    const { t, i18n } = useTranslation();
    
    const changeLang = (lang: string) => {
        i18n.changeLanguage(lang);
        localStorage.setItem('ui_language', lang);
    };

    return {
        t: (key: string, params?: any) => t(key, params),
        lang: i18n.language,
        changeLang,
    };
};
