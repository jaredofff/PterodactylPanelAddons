import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { ServerContext } from '@/state/server';
import { Spinner } from '@/components/elements/Spinner';
import { useFlash } from '@/plugins/useFlash';
import ConfirmationModal from '@/components/elements/ConfirmationModal';
import { useTranslation } from 'react-i18next';

interface Player {
    name: string;
    uuid: string;
    ping: number;
}

const PlayerList = () => {
    const { t } = useTranslation();
    const { addFlash } = useFlash();
    const uuid = ServerContext.useStoreState((state: any) => state.server.data!.uuid);
    
    const [players, setPlayers] = useState<Player[]>([]);
    const [loading, setLoading] = useState<boolean>(true);
    const [action, setAction] = useState<{ type: string, player: string } | null>(null);

    const fetchPlayers = () => {
        setLoading(true);
        axios.get(`/api/client/servers/${uuid}/ultimate-suite/players`)
            .then(({ data }: { data: { data: Player[] } }) => setPlayers(data.data))
            .catch(() => addFlash({ type: 'error', key: 'us:p', message: t('ultimate_suite.players.error_fetch') }))
            .finally(() => setLoading(false));
    };

    useEffect(() => { 
        fetchPlayers(); 
    }, [uuid]);

    const executeAction = () => {
        if (!action) return;
        axios.post(`/api/client/servers/${uuid}/ultimate-suite/players/command`, { command: action.type, player: action.player })
            .then(() => { 
                addFlash({ type: 'success', key: 'us:p', message: t('ultimate_suite.players.success_action', { action: action.type, player: action.player }) }); 
                fetchPlayers(); 
            })
            .catch(() => addFlash({ type: 'error', key: 'us:p', message: t('ultimate_suite.players.error_action') }))
            .finally(() => setAction(null));
    };

    return (
        <div className={'bg-neutral-800 p-6 rounded shadow-md border border-neutral-700 mt-4'}>
            <ConfirmationModal 
                visible={!!action} 
                title={t('ultimate_suite.players.confirm_title', { action: action?.type })} 
                buttonText={t('ultimate_suite.players.confirm_btn', { action: action?.type })} 
                onConfirmed={executeAction} 
                onDismissed={() => setAction(null)}
            >
                {t('ultimate_suite.players.confirm_msg', { action: action?.type, player: action?.player })}
            </ConfirmationModal>
            <div className={'flex justify-between items-center mb-6'}>
                <h2 className={'text-xl font-bold text-neutral-100'}>{t('ultimate_suite.players.title')}</h2>
                <button onClick={fetchPlayers} className={'bg-primary-600 hover:bg-primary-500 text-white px-3 py-1 rounded text-sm transition'}>
                    {t('ultimate_suite.players.refresh')}
                </button>
            </div>
            {loading ? <Spinner size={'small'} centered /> : (
                <table className={'w-full text-neutral-200'}>
                    <thead><tr className={'text-left border-b border-neutral-700'}><th className={'pb-3'}>Player</th><th className={'pb-3 text-right'}>Actions</th></tr></thead>
                    <tbody>
                        {players.map((p: Player) => (
                            <tr key={p.uuid} className={'border-b border-neutral-700/50'}>
                                <td className={'py-3'}>{p.name}</td>
                                <td className={'py-3 text-right'}>
                                    <button onClick={() => setAction({ type: 'kick', player: p.name })} className={'bg-yellow-600/20 text-yellow-500 px-2 py-1 rounded text-xs'}>Kick</button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            )}
        </div>
    );
};

export default PlayerList;
