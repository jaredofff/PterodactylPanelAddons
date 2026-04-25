#!/bin/bash

# Pterodactyl Ultimate Suite - Automatic Installer
# Created for production environments

echo "------------------------------------------------"
echo "  Pterodactyl Ultimate Suite - Installer"
echo "------------------------------------------------"

# Check if we are in the root directory of Pterodactyl
if [ ! -f "artisan" ]; then
    echo "Error: artisan file not found. Please run this script in the root of Pterodactyl."
    exit 1
fi

echo "Step 1/5: Installing dependencies..."
composer install --no-dev --optimize-autoloader

echo "Step 2/5: Running database migrations..."
php artisan migrate --force

echo "Step 3/5: Clearing cache..."
php artisan optimize:clear

echo "Step 4/5: Installing frontend dependencies..."
npm install

echo "Step 5/5: Building frontend assets..."
npm run build

echo "------------------------------------------------"
echo "  Installation Successful! Extension Ready."
echo "------------------------------------------------"
