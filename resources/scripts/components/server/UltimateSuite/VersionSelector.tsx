import React, { useState, ChangeEvent } from 'react';
import axios from 'axios';
import { ServerContext } from '@/state/server';
import { useFlash } from '@/plugins/useFlash';
import Button from '@/components/elements/Button';
import { useTranslation } from 'react-i18next';

const VersionSelector = () => {
    const { t } = useTranslation();
    const { addFlash } = useFlash();
    const uuid = ServerContext.useStoreState((state: any) => state.server.data!.uuid);
    const [type, setType] = useState<string>('paper');
    const [version, setVersion] = useState<string>('');
    const [loading, setLoading] = useState<boolean>(false);

    const updateVersion = () => {
        if (!version) return;
        setLoading(true);
        axios.post(`/api/client/servers/${uuid}/ultimate-suite/version`, { type, version })
            .then(() => addFlash({ type: 'success', key: 'us:v', message: t('ultimate_suite.version.success') }))
            .catch(() => addFlash({ type: 'error', key: 'us:v', message: t('ultimate_suite.version.error') }))
            .finally(() => setLoading(false));
    };

    return (
        <div className={'bg-neutral-800 p-6 rounded shadow-md border border-neutral-700'}>
            <h2 className={'text-xl font-bold mb-4 text-neutral-100'}>{t('ultimate_suite.version.title')}</h2>
            <div className={'grid grid-cols-1 md:grid-cols-2 gap-6'}>
                <select 
                    value={type} 
                    onChange={(e: ChangeEvent<HTMLSelectElement>) => setType(e.target.value)} 
                    className={'bg-neutral-900 border border-neutral-700 text-white p-2 rounded'}
                >
                    <option value="paper">PaperMC</option>
                    <option value="purpur">Purpur</option>
                </select>
                <input 
                    type="text" 
                    value={version} 
                    onChange={(e: ChangeEvent<HTMLInputElement>) => setVersion(e.target.value)} 
                    placeholder="1.20.4" 
                    className={'bg-neutral-900 border border-neutral-700 text-white p-2 rounded'} 
                />
            </div>
            <Button isLoading={loading} disabled={!version} onClick={updateVersion} className={'mt-4 w-full'}>
                {t('ultimate_suite.version.update_btn')}
            </Button>
        </div>
    );
};

export default VersionSelector;
