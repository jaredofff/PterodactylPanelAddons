# Pterodactyl Ultimate Suite - Professional Installation Guide

Follow these steps to deploy the Ultimate Suite on your Pterodactyl Panel.

## 📋 Prerequisites
If your server is fresh, make sure you have **Git** installed:
```bash
sudo apt update && sudo apt install git -y
```

## 📥 1. Cloning the Repository
Clone this repository to your server:
```bash
git clone https://github.com/jaredofff/PterodactylPanelAddons.git
cd PterodactylPanelAddons
```

## 🚀 2. Running the Installer
The installer handles everything: dependencies, database, Node.js installation, and frontend compilation.
```bash
chmod +x install.sh
sudo ./install.sh
```
> **Note**: Choose **Option 1** for a full fresh install (interactive) or **Option 2** for an existing panel.

## ⚙️ 3. Backend Registration
Add the ServiceProvider to `/var/www/pterodactyl/config/app.php`:
```php
'providers' => [
    // ...
    Pterodactyl\Providers\UltimateSuiteServiceProvider::class,
],
```

## 🎨 4. UI Integration
Edit `resources/scripts/components/server/ServerRouter.tsx` to add the links.
*Copy the full template from UltimateSuite/README.md for best results.*

---

## 🛠️ Troubleshooting & Recommendations

### 🔐 Permission Denied (EACCES)
If `npm run build` fails with permission errors:
```bash
sudo chown -R $USER:$USER /var/www/pterodactyl
npm run build
sudo chown -R www-data:www-data /var/www/pterodactyl
```

### 🧠 Low Memory (RAM) Crashes
If the build fails on small VPS (1GB RAM), the installer automatically creates a temporary SWAP file. If you run the build manually, ensure you have at least 2GB of swap.

### 📦 NPM Dependency Conflicts (ERESOLVE)
Always use the legacy flag to avoid conflicts with Pterodactyl's React 16 core:
```bash
npm install --save --legacy-peer-deps --force
```

---
Developed by **Jared** - Senior Pterodactyl Developer.
