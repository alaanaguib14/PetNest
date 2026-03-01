#!/bin/bash

echo "Starting PetNest..."

# Generate app key if not set
php artisan key:generate --force

# Run migrations
php artisan migrate --force

# Seed admin (safe — checks if admin exists first)
php artisan db:seed --class=AdminSeeder --force

# Cache everything for performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage symlink
php artisan storage:link

echo "Setup complete. Starting services..."

# Start PHP-FPM in background
php-fpm &

# Start Nginx in foreground (keeps container alive)
nginx -g "daemon off;"