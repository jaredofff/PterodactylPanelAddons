# Pterodactyl Ultimate Suite - Installation Guide

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
> **Note**: Choose **Option 1** for a full fresh install or **Option 2** to add the suite to an existing panel.

## ⚙️ 3. Backend Registration
Add the ServiceProvider to your Pterodactyl configuration in `/var/www/pterodactyl/config/app.php`:
```php
'providers' => [
    // ... other providers
    Pterodactyl\Providers\UltimateSuiteServiceProvider::class,
],
```

## 🎨 4. UI Integration
To show the buttons in the server sidebar, you need to add the routes to `ServerRouter.tsx`.
Refer to the integration guide in `UltimateSuite/README.md` for the exact code snippets.

---
Developed by **Jared** - Production Ready.
