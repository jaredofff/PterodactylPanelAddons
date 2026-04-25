import React from 'react';
import { NavLink, Route, Switch, useRouteMatch } from 'react-router-dom';
import VersionSelector from '@/components/server/UltimateSuite/VersionSelector';
import PlayerList from '@/components/server/UltimateSuite/PlayerList';
import LanguageSwitcher from '@/components/server/UltimateSuite/LanguageSwitcher';
import { ServerContext } from '@/state/server';

/**
 * MOCK ServerRouter for demonstration of integration.
 * In a real Pterodactyl installation, you would add these NavLinks and Routes
 * to resources/scripts/components/server/ServerRouter.tsx
 */
const ServerRouter = () => {
    const match = useRouteMatch();
    
    return (
        <div>
            {/* NavLinks Integration Example */}
            <div id={'navigation'}>
                <NavLink to={`${match.url}/ultimate-version`}>Version</NavLink>
                <NavLink to={`${match.url}/ultimate-players`}>Players</NavLink>
                <NavLink to={`${match.url}/ultimate-settings`}>Ultimate Settings</NavLink>
            </div>

            {/* Routes Integration Example */}
            <Switch>
                <Route path={`${match.url}/ultimate-version`} exact>
                    <VersionSelector />
                </Route>
                <Route path={`${match.url}/ultimate-players`} exact>
                    <PlayerList />
                </Route>
                <Route path={`${match.url}/ultimate-settings`} exact>
                    <LanguageSwitcher />
                </Route>
            </Switch>
        </div>
    );
};

export default ServerRouter;
