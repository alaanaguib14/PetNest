#!/bin/bash
set -e

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

# Start PHP-FPM FIRST and wait for it to be ready
php-fpm -D
sleep 3  # give PHP-FPM time to start

# Verify PHP-FPM is listening
echo "Checking PHP-FPM..."
netstat -tlnp | grep 8080 || echo "PHP-FPM NOT listening on 8080!"

echo "PHP-FPM started"

# Now run Laravel commands
php artisan migrate --force
php artisan db:seed --class=RoleSeeder --force
php artisan db:seed --class=AdminSeeder --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link


echo "Setup complete. Starting Nginx..."

# Start Nginx in foreground
nginx -g "daemon off;"