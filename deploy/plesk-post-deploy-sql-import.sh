#!/bin/bash
# Plesk Git deployment — SQL import sonrası (menu_one.sql)
# Git "Ek deployment komutları" alanına yapıştırın.
# migrate / db:seed YOK.

set -e

cd "${APPLICATION_ROOT:-/var/www/vhosts/trueddn.com.tr/panel.trueddn.com.tr}"

if [ ! -f .env ]; then
    echo "HATA: .env yok. deploy/plesk-panel.env içeriğini panel.trueddn.com.tr/.env olarak oluşturun."
    exit 1
fi

composer install --no-dev --optimize-autoloader --no-interaction

php artisan config:clear
php artisan deploy:prepare-production
php artisan storage:link --force 2>/dev/null || true

if command -v npm >/dev/null 2>&1; then
    npm install --include=dev --no-audit --no-fund
    npm run build
else
    echo "UYARI: npm yok. Bilgisayardan public/build klasörünü yükleyin (Git build içermez)."
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo "Deploy tamamlandı (SQL import modu): https://panel.trueddn.com.tr"
