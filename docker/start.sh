#!/bin/bash

echo "Starting PetNest..."

# Create .env file from Railway environment variables
cat > /var/www/.env << EOF
APP_NAME=${APP_NAME}
APP_ENV=${APP_ENV:-production}
APP_KEY=${APP_KEY}
APP_DEBUG=${APP_DEBUG:-false}
APP_URL=${APP_URL}

DB_CONNECTION=mysql
DB_HOST=${DB_HOST}
DB_PORT=${DB_PORT:-3306}
DB_DATABASE=${DB_DATABASE}
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}

JWT_SECRET=${JWT_SECRET}

QUEUE_CONNECTION=${QUEUE_CONNECTION:-sync}
CACHE_STORE=${CACHE_STORE:-file}

MAIL_MAILER=${MAIL_MAILER:-smtp}
MAIL_HOST=${MAIL_HOST}
MAIL_PORT=${MAIL_PORT:-2525}
MAIL_USERNAME=${MAIL_USERNAME}
MAIL_PASSWORD=${MAIL_PASSWORD}
MAIL_FROM_ADDRESS=${MAIL_FROM_ADDRESS}
MAIL_FROM_NAME="${APP_NAME}"
EOF

echo ".env file created"

# Generate app key if APP_KEY not set
if [ -z "${APP_KEY}" ]; then
    php artisan key:generate --force
fi

# Run migrations
php artisan migrate --force

# Seed Roles
php artisan db:seed --class=RoleSeeder --force

# Seed Admin
php artisan db:seed --class=AdminSeeder --force

# Cache everything
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Storage link
php artisan storage:link

echo "Setup complete. Starting services..."

php-fpm &
nginx -g "daemon off;"