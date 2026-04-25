import { useTranslation } from 'react-i18next';

export const useLang = () => {
    const { t, i18n } = useTranslation();
    return {
        t: (key: string, params?: any) => t(key, params),
        lang: i18n.language,
        changeLang: (lang: string) => {
            i18n.changeLanguage(lang);
            localStorage.setItem('ui_language', lang);
        },
    };
};
