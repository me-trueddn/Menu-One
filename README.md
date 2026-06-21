# Menu-One — Cloud Cafe Adisyon Sistemi

Çok kiracılı (multi-tenant) cafe adisyon SaaS uygulaması. Laravel 12 + PHP 8.2+, MariaDB/MySQL, AdminLTE 4 teması.

## Sürüm

| | |
|---|---|
| **Son build** | Build 1 — 1.0.27 |
| **Geliştirme sürümü** | 2.0.1 |
| **Testler** | 35 test geçiyor |

```bash
php artisan version:show      # mevcut sürüm + build geçmişi
php artisan version:bump        # patch artır (geliştirme)
php artisan version:build       # release build al
```

Footer'da `v{sürüm}` ve `Build {n}` etiketi gösterilir.

## Özellikler

### Platform (`/platform/*`)
- Cafe (tenant) ve lisans tipi yönetimi
- Kullanıcı / müşteri / personel yönetimi, 2FA, parola politikası
- Site & mail ayarları, tenant destek modu, impersonation

### Cafe Admin (`/admin/*`)
- Masa, kategori, ürün, personel CRUD
- Raporlar, operasyon ekranı (mutfak görünümü)

### Garson (`/waiter/*`)
- Masa grid, adisyon aç/kapat, ürün ekle/çıkar, mutfağa gönder
- **Hesabı kapat** → adisyon kasiyere `awaiting_payment` olarak gider
- Hazır sipariş polling (5 sn)
- Rezervasyon listesi ve masa detayında rezervasyon yönetimi

### Kasiyer (`/cashier/*`)
- Masa listesi ve ödeme ekranı
- Nakit / kredi kartı, bölünmüş ödeme (`split_count`: 0 = tam tutar, 1+ = kişi başı)
- Ödeme sonrası adisyon kapanır, masa boşalır

### Rezervasyonlar (`/reservations/*`)
- Garson, kasiyer ve cafe admin erişebilir
- Misafir adı, telefon, kişi sayısı, tarih/saat aralığı
- **Ara** butonu (`tel:`) — telefondan misafir arama
- Arama (ad, telefon, masa) ve sayfalama (10 / 50 / 150)
- Ödeme sonrası otomatik **Tamamlandı**; garson manuel **Tamamlandı işaretle** de kullanabilir
- Erken ayrılışta planlanan bitiş `scheduled_ends_at` alanında saklanır

### Mutfak (`/kitchen/*`)
- Bekleyen siparişler (5 sn polling), durum güncelleme

### Müşteri / Profil
- Cafe sahipleri profilden cafe oluşturabilir (Free / Premium lisans)
- Çoklu cafe, tenant seçimi, profil & 2FA ayarları

### Güvenlik & oturum
- Oturum boşta kalma süresi (platform ayarı, varsayılan 30 dk)
- Logout ve oturum süresi dolunca login ekranına yönlendirme (419 yerine)
- Tek oturum token doğrulama, parola süresi, 2FA zorunluluğu

## Sipariş durumları

| Durum | Açıklama |
|-------|----------|
| `open` | Adisyon açık, ürün eklenebilir |
| `sent` | Mutfağa gönderildi |
| `awaiting_payment` | Garson hesabı kapattı, kasiyer ödeme bekliyor |
| `closed` | Ödeme alındı, adisyon kapalı |

## Multi-tenant

Tek veritabanı + `tenant_id` (stancl/tenancy single-DB). Aktif tenant oturumda `active_tenant_id` ile seçilir.

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

> **Not:** Üretim ortamında SQLite kullanılmaz. Geliştirme/test için `phpunit.xml` SQLite `:memory:` kullanır.

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
| Kasiyer | cashier@demo-cafe.test | password |
| Mutfak | kitchen@demo-cafe.test | password |

## Route Grupları

| Prefix | Rol |
|--------|-----|
| `/platform/*` | platform_admin |
| `/admin/*` | cafe_admin |
| `/waiter/*` | waiter |
| `/cashier/*` | cashier, cafe_admin |
| `/kitchen/*` | kitchen |
| `/reservations/*` | waiter, cashier, cafe_admin |

## Tema Yapısı

Blade/HTML temalar `themes/` klasöründe tutulur. Aktif tema: `themes/adminlte4/` (AdminLTE 4.0.0 via npm).

```bash
npm install
npm run build
```

## Test

```bash
php artisan test
```

## MSSQL (İleride)

Migration'lar Laravel Schema Builder kullanır. `config/database.php` içinde `sqlsrv` connection şablonu mevcuttur.
