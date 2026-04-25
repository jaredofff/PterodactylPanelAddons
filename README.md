# Pterodactyl Ultimate Suite

![License](https://img.shields.io/badge/license-MIT-blue.svg)
![Version](https://img.shields.io/badge/version-1.0.0-green.svg)
![Pterodactyl](https://img.shields.io/badge/pterodactyl-1.x-orange.svg)

The **Ultimate Suite** is a high-end extension for the Pterodactyl Panel designed to empower server owners with advanced management tools, automated versioning, and real-time player oversight.

## 🚀 Main Modules

### 1. Version Manager
Change your server's Minecraft version and type (Paper, Purpur, Fabric, Forge) with a single click.
- Automatic environment variable updates (`MINECRAFT_VERSION`, `SERVER_TYPE`, `DOWNLOAD_URL`, `BUILD_NUMBER`).
- Automatic server reinstallation trigger.
- Dynamic download URL generation.

### 2. Player Manager
Real-time dashboard to monitor and manage online players.
- Live player list with avatars and pings.
- One-click actions: **Kick**, **Ban**, **Whitelist**.
- Direct command execution from the panel.

### 3. Multi-language System
Full internationalization support for a global user base.
- Built-in support for **English** and **Spanish**.
- User-specific language preferences saved in the database.
- Seamless React integration using `react-i18next`.

## 🛠️ Technical Overview
- **Backend**: Laravel (PHP 8.x) following Pterodactyl's service-layer architecture.
- **Frontend**: React (TypeScript) with TailwindCSS for a native look and feel.
- **Security**: Hardened controllers with FormRequest validation and dedicated audit logging.
- **Automation**: Custom Bash installer for seamless deployment.

## 📦 Installation

To install the extension, navigate to the `/UltimateSuite` directory and follow the instructions in its specific [README.md](./UltimateSuite/README.md).

```bash
# Quick Start
chmod +x UltimateSuite/install.sh
./UltimateSuite/install.sh
```

---
Developed by **Jared** - Production Ready Extension.
