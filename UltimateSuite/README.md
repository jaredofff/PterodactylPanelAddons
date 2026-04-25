# Pterodactyl Ultimate Suite

Professional extension for Pterodactyl Panel providing advanced server management tools.

## Features
- **Version Manager**: One-click server reinstallation with version selection.
- **Player Manager**: Real-time player list with Kick/Ban actions and pings.
- **Multi-language**: Native support for English and Spanish.
- **Production Ready**: Optimized React components and hardened Laravel backend.

## Requirements
- Pterodactyl Panel v1.x
- Node.js & NPM
- Composer

## Installation

1. Upload the `UltimateSuite` files to your Pterodactyl root directory.
2. Register the ServiceProvider in `config/app.php`:
   ```php
   'providers' => [
       // ...
       Pterodactyl\Providers\UltimateSuiteServiceProvider::class,
   ],
   ```
3. Run the installer:
   ```bash
   chmod +x install.sh
   ./install.sh
   ```

## Support
Contact us at support@example.com for any issues.
