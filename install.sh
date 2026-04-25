#!/bin/bash

# =================================================================
#  Pterodactyl Ultimate Suite - Pro Installer (Smart Version)
# =================================================================

set -e

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Rutas
PTERO_PATH="/var/www/pterodactyl"
EXT_SOURCE="./UltimateSuite"

echo -e "${BLUE}=== Pterodactyl Ultimate Suite Pro Installer ===${NC}"

# Validar Root
if [[ $EUID -ne 0 ]]; then
   echo -e "${RED}Error: Ejecuta como root (sudo).${NC}"
   exit 1
fi

echo "1) Instalación COMPLETA (Panel + Addon)"
echo "2) Instalar SOLO el Addon (En panel ya existente)"
echo "3) Salir"
read -p "Opción: " OPT

case $OPT in
    1)
        echo -e "${YELLOW}Iniciando instalador oficial interactivo...${NC}"
        bash <(curl -s https://pterodactyl-installer.se)
        echo -e "${GREEN}Instalación oficial finalizada.${NC}"
        ;;
    2)
        if [ ! -d "$PTERO_PATH" ]; then
            echo -e "${RED}Error: No se encontró Pterodactyl en $PTERO_PATH${NC}"
            exit 1
        fi
        ;;
    *)
        exit 0
        ;;
esac

# Lógica de instalación del Addon
if [ ! -d "$PTERO_PATH" ]; then
    echo -e "${RED}Error crítico: No se detectó la carpeta del panel en $PTERO_PATH${NC}"
    exit 1
fi

echo -e "${BLUE}Instalando Ultimate Suite Addon...${NC}"

# Backup de seguridad
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
cp -r "$PTERO_PATH" "/var/www/pterodactyl_backup_$TIMESTAMP"

# Copiar archivos del addon
cp -r "$EXT_SOURCE/app" "$PTERO_PATH/"
cp -r "$EXT_SOURCE/resources" "$PTERO_PATH/"
cp -r "$EXT_SOURCE/routes" "$PTERO_PATH/"
cp -r "$EXT_SOURCE/database" "$PTERO_PATH/"

cd "$PTERO_PATH"

# Automatizar ServiceProvider
if ! grep -q "UltimateSuiteServiceProvider" "config/app.php"; then
    echo -e "${YELLOW}Registrando ServiceProvider en config/app.php...${NC}"
    sed -i "/Pterodactyl\\\\Providers\\\\AppServiceProvider::class,/a \        Pterodactyl\\\\Providers\\\\UltimateSuiteServiceProvider::class," config/app.php
fi

# Optimización Backend
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear

# Node.js y Herramientas
if ! command -v npm &> /dev/null; then
    curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
    apt-get install -y nodejs
fi
if ! command -v yarn &> /dev/null; then
    npm install -g yarn
fi
yarn config set ignore-engines true || true

# DETECCIÓN DE VERSIÓN Y PARCHEO DE UI
ROUTER_PATH=""
if [ -f "resources/scripts/routers/ServerRouter.tsx" ]; then
    ROUTER_PATH="resources/scripts/routers/ServerRouter.tsx"
    echo -e "${GREEN}Detectado Panel v1.12.x+ (Router en carpeta routers)${NC}"
elif [ -f "resources/scripts/components/server/ServerRouter.tsx" ]; then
    ROUTER_PATH="resources/scripts/components/server/ServerRouter.tsx"
    echo -e "${GREEN}Detectado Panel v1.11.x o inferior (Router en carpeta components)${NC}"
fi

if [ ! -z "$ROUTER_PATH" ]; then
    echo -e "${BLUE}Parchando interfaz de usuario automáticamente...${NC}"
    # Inyectar botones si no existen
    if ! grep -q "ultimate-suite" "$ROUTER_PATH"; then
        # Buscamos el cierre de SidePanel o NavigationContainer para insertar antes
        sed -i "/<\/SidePanel>/i \                <div className={'mt-4 border-t border-gray-700 pt-4'}>\n                    <NavLink to={\`\${match.url}/ultimate-suite/version\`}>\n                        <FontAwesomeIcon icon={faMicrochip} /> <span style={{color: '#00ffff'}}>Version Manager</span>\n                    </NavLink>\n                </div>" "$ROUTER_PATH"
        sed -i "/<\/NavigationContainer>/i \                <div className={'mt-4 border-t border-gray-700 pt-4'}>\n                    <NavLink to={\`\${match.url}/ultimate-suite/version\`}>\n                        <FontAwesomeIcon icon={faMicrochip} /> <span style={{color: '#00ffff'}}>Version Manager</span>\n                    </NavLink>\n                </div>" "$ROUTER_PATH"
        
        # Inyectar Rutas
        sed -i "/<\/Switch>/i \                    <Route path={\`\${match.path}/ultimate-suite/version\`} component={VersionSelector} exact />" "$ROUTER_PATH"
    fi
fi

# GESTIÓN DE MEMORIA (SWAP TEMPORAL)
if [ ! -f /swapfile_ptero ]; then
    fallocate -l 2G /swapfile_ptero || dd if=/dev/zero of=/swapfile_ptero bs=1M count=2048
    chmod 600 /swapfile_ptero && mkswap /swapfile_ptero && swapon /swapfile_ptero
fi

# Frontend Build
echo -e "${BLUE}Compilando assets (esto puede tardar unos minutos)...${NC}"
npm config set legacy-peer-deps true
npm install --force --legacy-peer-deps
npm run build:production

# Limpieza
[ -f /swapfile_ptero ] && swapoff /swapfile_ptero && rm /swapfile_ptero
chown -R www-data:www-data "$PTERO_PATH"

echo -e "${GREEN}================================================${NC}"
echo -e "${GREEN}  ¡Instalación Exitosa! Todo está listo.        ${NC}"
echo -e "${GREEN}================================================${NC}"
