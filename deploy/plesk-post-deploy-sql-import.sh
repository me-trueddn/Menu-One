#!/bin/bash
# Plesk Git deployment — SQL import sonrası (menu_one.sql)
# vendor/ ve public/build/ Git ile gelir; composer/npm yedek olarak çalışır.

set -e

cd "${APPLICATION_ROOT:-/var/www/vhosts/trueddn.com.tr/panel.trueddn.com.tr}"

if [ ! -f .env ]; then
    echo "HATA: .env yok. deploy/plesk-panel.env içeriğini panel.trueddn.com.tr/.env olarak oluşturun."
    exit 1
fi

if [ ! -d vendor/spatie/laravel-permission ]; then
    echo "vendor Git'te yok veya eksik — composer install çalıştırılıyor..."
    composer install --no-dev --optimize-autoloader --no-interaction
fi

if ! php -r 'require "vendor/autoload.php"; exit(class_exists(\Spatie\Permission\PermissionServiceProvider::class) && class_exists(\Laravel\Socialite\SocialiteServiceProvider::class) ? 0 : 1);'; then
    echo "HATA: vendor eksik (spatie/permission veya socialite yok)."
    exit 1
fi

php artisan package:discover --ansi

php artisan optimize:clear
php artisan config:clear
php artisan deploy:prepare-production
php artisan storage:link --force 2>/dev/null || true

if [ ! -f public/build/manifest.json ]; then
    echo "public/build eksik — npm build çalıştırılıyor..."
    if command -v npm >/dev/null 2>&1; then
        npm install --include=dev --no-audit --no-fund
        npm run build
    else
        echo "UYARI: npm yok ve build yok. Git'te public/build commit edin."
    fi
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan version:show
php artisan tenants:list || {
    echo "HATA: Tenant ID uyumsuz — v2.0.62+ kodu deploy edildi mi? optimize:clear + PHP-FPM restart deneyin."
    exit 1
}

chmod -R ug+rwx,o-rwx storage bootstrap/cache 2>/dev/null || true

echo "Deploy tamamlandı (SQL import modu): https://panel.trueddn.com.tr"
