# Menu-One — Cloud Cafe Adisyon Sistemi

Çok kiracılı (multi-tenant) cafe adisyon SaaS uygulaması. Laravel 12 + PHP 8.2+, MariaDB/MySQL, AdminLTE 4 teması.

## Özellikler

- **Platform Admin:** Cafe (tenant) yönetimi
- **Cafe Admin:** Masa, kategori, ürün, personel CRUD + raporlar
- **Garson:** Masa grid, adisyon aç/kapat, ürün ekle, mutfağa gönder
- **Mutfak:** Bekleyen siparişler (5 sn polling), durum güncelleme
- **Multi-tenant:** Tek veritabanı + `tenant_id` (stancl/tenancy single-DB)

## Gereksinimler

- PHP 8.2+
- Composer
- Node.js 18+
- MariaDB/MySQL (veya geliştirme için SQLite)

## Kurulum (Yerel — XAMPP / MariaDB)

```bash
composer install
cp .env.example .env
php artisan key:generate
```

**Veritabanı (XAMPP MariaDB):**

1. XAMPP Control Panel'den **MySQL** servisini başlatın
2. Veritabanını oluşturun (phpMyAdmin veya CLI):

```bash
# XAMPP mysql yolu örneği
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE menu_one CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

3. `.env` dosyasında MySQL ayarları (XAMPP varsayılan):

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=menu_one
DB_USERNAME=root
DB_PASSWORD=
```

4. Migration ve demo veriler:

```bash
php artisan migrate:fresh --seed
npm install && npm run build
php artisan serve
```

Giriş: http://127.0.0.1:8000/login

> **Not:** SQLite artık kullanılmıyor. Eski `database/database.sqlite` dosyası silinebilir.

## Docker

```bash
docker compose up -d
docker compose exec app php artisan migrate --seed
docker compose exec app npm install && npm run build
```

Uygulama: http://localhost:8000

## Giriş Sorunu?

`.env` dosyasında `APP_KEY` boşsa oturum açılamaz. Şunu çalıştırın:

```bash
php artisan key:generate
php artisan config:clear
```

Ardından sunucuyu yeniden başlatın: `php artisan serve`

## Dil Desteği

Varsayılan dil **Türkçe** (`APP_LOCALE=tr`). İngilizce için navbar veya giriş sayfasındaki dil seçicisini kullanın.

Çeviri dosyaları:
- `lang/tr/menu.php` — uygulama metinleri
- `lang/en/menu.php` — İngilizce karşılıklar
- `lang/tr/auth.php` — giriş hata mesajları

Yeni dil eklemek için `config/locale.php` içine locale kodunu ekleyin ve `lang/{kod}/menu.php` oluşturun.

## Demo Hesaplar

| Rol | E-posta | Şifre |
|-----|---------|-------|
| Platform Admin | admin@menu-one.test | password |
| Cafe Admin | admin@demo-cafe.test | password |
| Garson | waiter@demo-cafe.test | password |
| Mutfak | kitchen@demo-cafe.test | password |

## Tema Yapısı

Blade/HTML temalar `themes/` klasöründe tutulur. Aktif tema: `themes/adminlte4/` (AdminLTE 4.0.0 via npm).

```bash
npm install admin-lte@4.0.0 bootstrap @popperjs/core bootstrap-icons
```

## Route Grupları

| Prefix | Rol |
|--------|-----|
| `/platform/*` | platform_admin |
| `/admin/*` | cafe_admin |
| `/waiter/*` | waiter |
| `/kitchen/*` | kitchen |

## MSSQL (İleride)

Migration'lar Laravel Schema Builder kullanır. `config/database.php` içinde `sqlsrv` connection şablonu mevcuttur.

## Test

```bash
php artisan test
```
