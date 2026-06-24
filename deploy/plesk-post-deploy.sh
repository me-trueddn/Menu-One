#!/bin/bash
# Plesk deployment actions — panel.trueddn.com.tr
# Plesk Git deployment — boş veritabanı (migrate + seed)
# SQL import kullanıyorsanız: deploy/plesk-post-deploy-sql-import.sh
# Kurulum rehberi: deploy/plesk-git-kurulum.md
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

if ! php -r 'require "vendor/autoload.php"; exit(class_exists(\Spatie\Permission\PermissionServiceProvider::class) && class_exists(\Laravel\Socialite\SocialiteServiceProvider::class) ? 0 : 1);'; then
    echo "HATA: vendor eksik (spatie/permission veya socialite yok). composer install tekrar çalıştırın."
    exit 1
fi

php artisan package:discover --ansi

php artisan config:clear

if ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
    php artisan key:generate --force
fi

php artisan migrate --force
php artisan db:seed --force
php artisan storage:link --force 2>/dev/null || true

if command -v npm >/dev/null 2>&1; then
    npm install --no-audit --no-fund
    npm run build
else
    echo "UYARI: npm bulunamadı. Plesk Node.js eklentisini kurun veya public/build klasörünü yükleyin."
fi

php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

chmod -R ug+rwx,o-rwx storage bootstrap/cache 2>/dev/null || true

echo "Deploy tamamlandı: https://panel.trueddn.com.tr"
