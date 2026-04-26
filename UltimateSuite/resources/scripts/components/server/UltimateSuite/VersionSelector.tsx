import React, { useState, useEffect, ChangeEvent } from 'react';
import axios from 'axios';
import { ServerContext } from '@/state/server';
import useFlash from '@/plugins/useFlash';
import Button from '@/components/elements/Button';
import { useTranslation } from 'react-i18next';
import Spinner from '@/components/elements/Spinner';

interface JarType {
    name: string;
    icon: string;
    color: string;
}

interface JarVersion {
    version: string;
    is_latest: boolean;
}

const VersionSelector = () => {
    const { t } = useTranslation();
    const { addFlash, clearFlashes } = useFlash();
    const uuid = ServerContext.useStoreState((state: any) => state.server.data!.uuid);
    
    const [type, setType] = useState('PAPER');
    const [version, setVersion] = useState('');
    const [loading, setLoading] = useState(false);
    const [loadingTypes, setLoadingTypes] = useState(true);
    const [loadingVersions, setLoadingVersions] = useState(false);
    
    const [types, setTypes] = useState<Record<string, JarType>>({});
    const [versions, setVersions] = useState<JarVersion[]>([]);

    useEffect(() => {
        setLoadingTypes(true);
        axios.get(`/api/client/servers/${uuid}/ultimate-suite/version/types`)
            .then(({ data }: { data: { types: Record<string, JarType> } }) => {
                setTypes(data.types);
                if (Object.keys(data.types).length > 0) {
                    fetchVersions('PAPER');
                }
            })
            .catch((err: Error) => console.error('Failed to fetch types:', err))
            .finally(() => setLoadingTypes(false));
    }, [uuid]);

    const fetchVersions = (selectedType: string) => {
        setLoadingVersions(true);
        setVersions([]);
        setVersion('');
        axios.get(`/api/client/servers/${uuid}/ultimate-suite/version/types/${selectedType}`)
            .then(({ data }: { data: { versions: JarVersion[] } }) => {
                setVersions(data.versions);
                if (data.versions.length > 0) {
                    setVersion(data.versions[0].version);
                }
            })
            .catch((err: Error) => console.error('Failed to fetch versions:', err))
            .finally(() => setLoadingVersions(false));
    };

    const handleTypeChange = (e: ChangeEvent<HTMLSelectElement>) => {
        const newType = e.target.value;
        setType(newType);
        fetchVersions(newType);
    };

    const updateVersion = () => {
        if (!version) return;
        setLoading(true);
        clearFlashes('us:v');
        
        axios.post(`/api/client/servers/${uuid}/ultimate-suite/version`, { type, version })
            .then(() => addFlash({ 
                type: 'success', 
                key: 'us:v', 
                message: t('ultimate_suite.version.success') || 'Server version updated and reinstalling.' 
            }))
            .catch(() => addFlash({ 
                type: 'error', 
                key: 'us:v', 
                message: t('ultimate_suite.version.error') || 'Failed to update server version.' 
            }))
            .finally(() => setLoading(false));
    };

    return (
        <div className={'bg-neutral-800 p-6 rounded shadow-md border border-neutral-700'}>
            <h2 className={'text-xl font-bold mb-4 text-neutral-100 flex items-center'}>
                <span className={'mr-2'}>🛠️</span>
                {t('ultimate_suite.version.title') || 'Version Manager'}
            </h2>
            
            <div className={'grid grid-cols-1 md:grid-cols-2 gap-6'}>
                <div>
                    <label className={'block text-sm font-medium text-neutral-400 mb-2'}>
                        {t('ultimate_suite.version.type') || 'Server Type'}
                    </label>
                    <div className={'relative'}>
                        <select 
                            value={type} 
                            onChange={handleTypeChange} 
                            disabled={loadingTypes}
                            className={'w-full bg-neutral-900 border border-neutral-700 text-white p-2.5 rounded focus:ring-blue-500 focus:border-blue-500'}
                        >
                            {loadingTypes ? (
                                <option disabled>{t('ultimate_suite.version.loading_types') || 'Loading...'}</option>
                            ) : (
                                Object.entries(types).map(([key, info]: [string, JarType]) => (
                                    <option key={key} value={key}>{info.name}</option>
                                ))
                            )}
                        </select>
                        {loadingTypes && <div className={'absolute right-8 top-3'}><Spinner size={'small'}/></div>}
                    </div>
                </div>

                <div>
                    <label className={'block text-sm font-medium text-neutral-400 mb-2'}>
                        {t('ultimate_suite.version.version') || 'Minecraft Version'}
                    </label>
                    <div className={'relative'}>
                        <select 
                            value={version} 
                            onChange={(e: ChangeEvent<HTMLSelectElement>) => setVersion(e.target.value)} 
                            disabled={loadingVersions || !type}
                            className={'w-full bg-neutral-900 border border-neutral-700 text-white p-2.5 rounded focus:ring-blue-500 focus:border-blue-500'}
                        >
                            {!version && <option value="">{t('ultimate_suite.version.select_version') || 'Select version'}</option>}
                            {versions.map((v: JarVersion) => (
                                <option key={v.version} value={v.version}>{v.version}</option>
                            ))}
                        </select>
                        {loadingVersions && <div className={'absolute right-8 top-3'}><Spinner size={'small'}/></div>}
                    </div>
                </div>
            </div>

            <div className={'mt-8 flex items-center justify-between'}>
                <p className={'text-xs text-neutral-500 italic'}>
                    Powered by <a href="https://mcjars.app" target="_blank" rel="noreferrer" className={'text-blue-400 hover:underline'}>mcjars.app</a>
                </p>
                <Button 
                    isLoading={loading} 
                    disabled={!version || loading} 
                    onClick={updateVersion} 
                    className={'px-8 shadow-lg'}
                >
                    {t('ultimate_suite.version.update_btn') || 'Update & Reinstall'}
                </Button>
            </div>
        </div>
    );
};

export default VersionSelector;
