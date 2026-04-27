#!/bin/bash

# =================================================================
#  Pterodactyl Addon Manager - Pro Installer (Unified Version)
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
ULTIMATE_SOURCE="./UltimateSuite"
GPT_SOURCE="./PteroGPT"

echo -e "${BLUE}================================================${NC}"
echo -e "${BLUE}   Pterodactyl Addon Manager - Pro Installer    ${NC}"
echo -e "${BLUE}================================================${NC}"

# Validar Root
if [[ $EUID -ne 0 ]]; then
   echo -e "${RED}Error: Ejecuta como root (sudo).${NC}"
   exit 1
fi

echo -e "${YELLOW}Selecciona una opción de instalación:${NC}"
echo "1) Instalar Ultimate Suite solamente"
echo "2) Instalar PteroGPT solamente"
echo "3) Instalar AMBOS (Full Pack)"
echo "4) Salir"
read -p "Opción: " MAIN_OPT

INSTALL_ULTIMATE=false
INSTALL_GPT=false

case $MAIN_OPT in
    1) INSTALL_ULTIMATE=true ;;
    2) INSTALL_GPT=true ;;
    3) INSTALL_ULTIMATE=true; INSTALL_GPT=true ;;
    *) exit 0 ;;
esac

# Validar Panel
if [ ! -d "$PTERO_PATH" ]; then
    echo -e "${RED}Error: No se encontró Pterodactyl en $PTERO_PATH${NC}"
    exit 1
fi

# Backup
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
echo -e "${YELLOW}Creando backup en /var/www/pterodactyl_backup_$TIMESTAMP...${NC}"
cp -r "$PTERO_PATH" "/var/www/pterodactyl_backup_$TIMESTAMP"

# --- FUNCIONES DE PARCHEO ---

patch_pterogpt() {
    echo -e "${BLUE}Aplicando parches de PteroGPT...${NC}"
    
    # app/Console/Kernel.php
    if ! grep -q "pterogpt:prune" "$PTERO_PATH/app/Console/Kernel.php"; then
        sed -i "/telemetry.enabled/a \        \$schedule->command('pterogpt:prune')->daily();" "$PTERO_PATH/app/Console/Kernel.php"
    fi

    # routes/admin.php
    if ! grep -q "pterogpt" "$PTERO_PATH/routes/admin.php"; then
        sed -i "/Route::patch('\/advanced'/a \Route::get('/pterogpt', [Admin\\\\Settings\\\\PteroGPTController::class, 'index'])->name('admin.settings.pterogpt');\nRoute::patch('/pterogpt', [Admin\\\\Settings\\\\PteroGPTController::class, 'update']);" "$PTERO_PATH/routes/admin.php"
    fi

    # resources/scripts/routers/ServerRouter.tsx
    ROUTER_PATH=""
    if [ -f "$PTERO_PATH/resources/scripts/routers/ServerRouter.tsx" ]; then
        ROUTER_PATH="$PTERO_PATH/resources/scripts/routers/ServerRouter.tsx"
    elif [ -f "$PTERO_PATH/resources/scripts/components/server/ServerRouter.tsx" ]; then
        ROUTER_PATH="$PTERO_PATH/resources/scripts/components/server/ServerRouter.tsx"
    fi

    if [ ! -z "$ROUTER_PATH" ] && ! grep -q "PteroGPTProvider" "$ROUTER_PATH"; then
        sed -i "1i import { PteroGPTProvider, PteroGPTPanel, PteroGPTModal, PteroGPTButton, ErrorDetectionModal } from '@/components/server/pterogpt';" "$ROUTER_PATH"
        sed -i "s/<CSSTransition/<PteroGPTProvider>\n        <CSSTransition/g" "$ROUTER_PATH"
        sed -i "s/<\/ErrorBoundary>/<\/ErrorBoundary>\n        <PteroGPTButton \/>\n        <PteroGPTPanel \/>\n        <PteroGPTModal \/>\n        <ErrorDetectionModal \/>\n    <\/PteroGPTProvider>/g" "$ROUTER_PATH"
    fi
}

# --- PROCESO DE COPIA ---

if [ "$INSTALL_ULTIMATE" = true ]; then
    echo -e "${GREEN}Copiando archivos de Ultimate Suite...${NC}"
    cp -r "$ULTIMATE_SOURCE/app" "$PTERO_PATH/"
    cp -r "$ULTIMATE_SOURCE/resources" "$PTERO_PATH/"
    cp -r "$ULTIMATE_SOURCE/routes" "$PTERO_PATH/"
    cp -r "$ULTIMATE_SOURCE/database" "$PTERO_PATH/"
    
    # Registro de ServiceProvider
    if ! grep -q "UltimateSuiteServiceProvider" "$PTERO_PATH/config/app.php"; then
        sed -i "/AppServiceProvider::class,/a \        Pterodactyl\\\\Providers\\\\UltimateSuiteServiceProvider::class," "$PTERO_PATH/config/app.php"
    fi
fi

if [ "$INSTALL_GPT" = true ]; then
    echo -e "${GREEN}Copiando archivos de PteroGPT...${NC}"
    cp -r "$GPT_SOURCE/app" "$PTERO_PATH/"
    cp -r "$GPT_SOURCE/resources" "$PTERO_PATH/"
    cp -r "$GPT_SOURCE/routes" "$PTERO_PATH/"
    cp -r "$GPT_SOURCE/database" "$PTERO_PATH/"
    patch_pterogpt
fi

# --- FINALIZACIÓN ---

cd "$PTERO_PATH"
export COMPOSER_ALLOW_SUPERUSER=1

echo -e "${YELLOW}Instalando dependencias de PHP...${NC}"
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear

echo -e "${YELLOW}Instalando dependencias de Node.js...${NC}"
if [ "$INSTALL_GPT" = true ]; then
    npm install react-markdown remark-gfm --save
fi
npm install --force
echo -e "${BLUE}Compilando assets...${NC}"
npm run build:production

chown -R www-data:www-data "$PTERO_PATH"

echo -e "${GREEN}================================================${NC}"
echo -e "${GREEN}  ¡Instalación Exitosa! Todo está listo.        ${NC}"
echo -e "${GREEN}================================================${NC}"
