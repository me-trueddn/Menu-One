#!/bin/bash
# Plesk deployment actions — panel.trueddn.com.tr
# Plesk Git "Ek deployment komutları" alanına yapıştırılabilir.

set -e

cd "${APPLICATION_ROOT:-/var/www/vhosts/trueddn.com.tr/panel.trueddn.com.tr}"

if [ ! -f .env ]; then
    echo "HATA: .env dosyası yok. Önce deploy/plesk-panel.env içeriğini Plesk Environment alanına yapıştırın."
    exit 1
fi

if ! grep -q '^DB_DATABASE=' .env || grep -q '^DB_DATABASE=$' .env; then
    echo "HATA: .env içinde DB_DATABASE ve diğer DB ayarlarını doldurun."
    exit 1
fi

composer install --no-dev --optimize-autoloader --no-interaction

php artisan config:clear

if ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
    php artisan key:generate --force
fi

php artisan migrate --force
php artisan db:seed --force
php artisan storage:link --force 2>/dev/null || true

if command -v npm >/dev/null 2>&1; then
    npm ci
    npm run build
else
    echo "UYARI: npm bulunamadı. Plesk Node.js eklentisini kurun veya public/build klasörünü yükleyin."
fi

php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo "Deploy tamamlandı: https://panel.trueddn.com.tr"
