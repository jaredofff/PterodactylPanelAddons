# Ultimate Suite - UI Integration Guide (v1.12.2+)

To show the buttons in the server sidebar, follow these instructions tailored for the latest Pterodactyl versions.

## 📍 File Location
In Pterodactyl v1.12.2 and above, the router is located at:
`/var/www/pterodactyl/resources/scripts/routers/ServerRouter.tsx`

## 🧩 Manual Injection
If the automatic installer didn't catch your layout, add these lines manually:

### 1. Imports
Add these at the top of `ServerRouter.tsx`:
```tsx
// Ultimate Suite Components
import VersionSelector from '@/components/server/UltimateSuite/VersionSelector';
import PlayerList from '@/components/server/UltimateSuite/PlayerList';
import LanguageSwitcher from '@/components/server/UltimateSuite/LanguageSwitcher';
```

### 2. Sidebar Buttons
Inside the `<SubNavigation>` component, add the links:
```tsx
{/* ULTIMATE SUITE BUTTONS */}
<NavLink to={to('ultimate-suite/version', true)} style={{color: '#00ffff'}}>
    Version Manager
</NavLink>
<NavLink to={to('ultimate-suite/players', true)} style={{color: '#00ffff'}}>
    Player Manager
</NavLink>
```

### 3. Switch Routes
Inside the `<Switch>` component, add the routes:
```tsx
{/* ULTIMATE SUITE ROUTES */}
<Route path={to('ultimate-suite/version')} component={VersionSelector} exact />
<Route path={to('ultimate-suite/players')} component={PlayerList} exact />
<Route path={to('ultimate-suite/settings')} component={LanguageSwitcher} exact />
```

## 🏗️ Building the Assets
After any UI change, you MUST recompile:
```bash
cd /var/www/pterodactyl
sudo chown -R $USER:$USER /var/www/pterodactyl
yarn config set ignore-engines true
npm run build:production
sudo chown -R www-data:www-data /var/www/pterodactyl
```
