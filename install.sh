#!/bin/bash

# =================================================================
#  Pterodactyl Ultimate Suite - All-in-One Installer (Hardened)
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

echo -e "${BLUE}=== Pterodactyl Ultimate Suite Installer (Hardened) ===${NC}"

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
        echo -e "${YELLOW}Iniciando instalación oficial de Pterodactyl...${NC}"
        bash <(curl -s https://pterodactyl-installer.se) <<EOF
0
0
admin
admin
admin
admin
admin@example.com
password
$HOSTNAME
$HOSTNAME
y
y
y
EOF
        echo -e "${GREEN}Panel instalado. Procediendo con el Addon...${NC}"
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
echo -e "${BLUE}Instalando Ultimate Suite Addon...${NC}"

# Backup
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
cp -r "$PTERO_PATH" "/var/www/pterodactyl_backup_$TIMESTAMP"

# Copiar archivos
if [ -d "$EXT_SOURCE" ]; then
    cp -r "$EXT_SOURCE/app" "$PTERO_PATH/"
    cp -r "$EXT_SOURCE/resources" "$PTERO_PATH/"
    cp -r "$EXT_SOURCE/routes" "$PTERO_PATH/"
    cp -r "$EXT_SOURCE/database" "$PTERO_PATH/"
else
    cp -r "../app" "$PTERO_PATH/"
    cp -r "../resources" "$PTERO_PATH/"
    cp -r "../routes" "$PTERO_PATH/"
    cp -r "../database" "$PTERO_PATH/"
fi

cd "$PTERO_PATH"

# Permitir Composer como root
export COMPOSER_ALLOW_SUPERUSER=1

# Ejecutar comandos de Laravel
echo -e "${BLUE}Optimizando Backend...${NC}"
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear

# Verificar e instalar Node.js/NPM si falta
if ! command -v npm &> /dev/null; then
    echo -e "${YELLOW}NPM no detectado. Instalando Node.js 18...${NC}"
    curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
    apt-get install -y nodejs
fi

# GESTIÓN DE MEMORIA (SWAP TEMPORAL)
# Esto evita que el build falle en servidores con poca RAM
if [ ! -f /swapfile_ptero ]; then
    echo -e "${YELLOW}Creando SWAP temporal (2GB) para evitar crashes de memoria...${NC}"
    fallocate -l 2G /swapfile_ptero || dd if=/dev/zero of=/swapfile_ptero bs=1M count=2048
    chmod 600 /swapfile_ptero
    mkswap /swapfile_ptero
    swapon /swapfile_ptero
    echo -e "${GREEN}SWAP activado.${NC}"
fi

# Frontend
echo -e "${BLUE}Limpiando entorno de Node...${NC}"
rm -rf node_modules package-lock.json

echo -e "${BLUE}Instalando dependencias (Forced Mode)...${NC}"
# Usamos --force y --legacy-peer-deps para resolver conflictos agresivamente
npm install --force --legacy-peer-deps

echo -e "${BLUE}Compilando assets de producción...${NC}"
# Aumentamos el límite de memoria de Node para el build
export NODE_OPTIONS="--max-old-space-size=2048"
npm run build

# Desactivar SWAP temporal al finalizar
if [ -f /swapfile_ptero ]; then
    swapoff /swapfile_ptero
    rm /swapfile_ptero
    echo -e "${YELLOW}SWAP temporal removido.${NC}"
fi

# Permisos
chown -R www-data:www-data "$PTERO_PATH"

echo -e "${GREEN}================================================${NC}"
echo -e "${GREEN}  ¡Instalación Exitosa! Todo está listo.        ${NC}"
echo -e "${GREEN}================================================${NC}"
