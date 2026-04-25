#!/bin/bash

# =================================================================
#  Pterodactyl Ultimate Suite - All-in-One Installer
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

echo -e "${BLUE}=== Pterodactyl Ultimate Suite Installer ===${NC}"

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
        # Usamos el instalador oficial de la comunidad para asegurar que el panel quede perfecto
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
        # Continuar con el addon...
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
cp -r "$EXT_SOURCE/app" "$PTERO_PATH/"
cp -r "$EXT_SOURCE/resources" "$PTERO_PATH/"
cp -r "$EXT_SOURCE/routes" "$PTERO_PATH/"
cp -r "$EXT_SOURCE/database" "$PTERO_PATH/"

cd "$PTERO_PATH"

# Ejecutar comandos de Laravel
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear

# Frontend
npm install
npm run build

# Permisos
chown -R www-data:www-data "$PTERO_PATH"

echo -e "${GREEN}================================================${NC}"
echo -e "${GREEN}  ¡Instalación Exitosa! Todo está listo.        ${NC}"
echo -e "${GREEN}================================================${NC}"
