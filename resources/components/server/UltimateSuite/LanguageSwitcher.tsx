import React, { useState } from 'react';
import axios from 'axios';
import { useTranslation } from 'react-i18next';

const LanguageSwitcher = () => {
    const { t, i18n } = useTranslation();
    const [language, setLanguage] = useState(i18n.language);

    const updateLanguage = (lang: string) => {
        setLanguage(lang);
        axios.post('/api/client/account/language', { language: lang })
            .then(() => {
                i18n.changeLanguage(lang);
                localStorage.setItem('ui_language', lang);
                window.location.reload(); // Reload to apply global changes
            })
            .catch(console.error);
    };

    return (
        <div className={'bg-neutral-800 p-4 rounded shadow-md border border-neutral-700'}>
            <label className={'block text-sm font-medium text-neutral-400 mb-2'}>{t('ultimate_suite.language.title')}</label>
            <select
                value={language}
                onChange={(e) => updateLanguage(e.target.value)}
                className={'w-full bg-neutral-900 border border-neutral-700 text-white rounded p-2 focus:ring-1 focus:ring-primary-500 outline-none'}
            >
                <option value="en">English</option>
                <option value="es">Español</option>
            </select>
        </div>
    );
};

export default LanguageSwitcher;
