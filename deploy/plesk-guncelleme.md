# Menu-One — Plesk güncelleme (Git pull)

`vendor/` ve `public/build/` **Git ile gelir** (Plesk Composer/npm yedek).

---

## Bilgisayarda (kod değişikliği sonrası)

`composer.lock` veya `package.json` değiştiyse önce yerelde:

```powershell
composer install --no-dev
npm run build
git add vendor public/build
```

Sonra normal commit + push:

```powershell
git add .
git commit -m "..."
git push origin main
```

---

## Plesk'te

1. **Menu-One.git** → **Şimdi çek** → **Şimdi dağıt**
2. `laravel_aa176b` ile **aynı anda dağıtma** — o depo iskelet kodu yazar

**Menu-One.git** → ⚙ → **Ek deployment komutları**: `deploy/plesk-post-deploy-sql-import.sh`

---

## Her pull sonrası (otomatik script)

- vendor/build Git'ten kopyalanır
- `deploy:prepare-production`
- cache

Elle zip **gerekmez** (vendor/build Git'te güncel ise).

---

## composer.lock değişince (önemli)

Plesk Composer güvenilir değil → **vendor'ı PC'de güncelleyip Git'e commit edin:**

```powershell
composer update paket-adi --no-dev
# veya
composer install --no-dev
git add composer.lock vendor
git commit -m "chore: update vendor"
git push
```

---

## Özet

```
Geliştirme  → composer install + npm run build (gerekirse)
Commit      → vendor + public/build dahil
Plesk       → Menu-One.git pull + deploy
```

---

## Her değişiklikte Artisan (Plesk — `php artisan` yazmayın)

| Ne değişti? | Plesk Artisan |
|-------------|---------------|
| **Her deploy** | `optimize:clear` → `deploy:prepare-production` → `config:cache` → `route:cache` → `view:cache` → `version:show` → `tenants:list` |
| Yeni migration | `deploy:prepare-production` (içinde `migrate --force`) |
| Sadece `.env` | `config:clear` → `config:cache` |
| Sadece route | `route:cache` |
| Sadece Blade | `view:clear` → `view:cache` |
| Frontend (Vite) | Node: `ci` → `run build` |
| `composer.lock` | PC'de `composer install --no-dev` + vendor commit; gerekirse Plesk Composer: `install --no-dev --optimize-autoloader` |

**Not:** `deploy:prepare-production` session/cache temizler → kullanıcılar yeniden giriş yapar. **users / tenants / müşteri kayıtlarına dokunmaz.**

---

## Canlı veri güvenliği (önemli)

| Komut | Canlıda? | Etki |
|--------|----------|------|
| `deploy:prepare-production` | Evet | Sadece cache, sessions, user_login_tokens temizler + `migrate --force` |
| `deploy:check-production` | Evet (önce/sonra) | users/tenants sayısını raporlar |
| `migrate --force` | Evet | Yeni tablo/kolon ekler; mevcut veriyi silmez |
| `optimize:clear` / `config:cache` / `route:cache` / `view:cache` | Evet | Önbellek |
| `tenants:repair-data` | Evet | Tenant ID onarımı; kullanıcı silmez |

| Komut | Canlıda? | Etki |
|--------|----------|------|
| `migrate:fresh` | **HAYIR** | Tüm tabloları siler |
| `db:wipe` | **HAYIR** | Tüm tabloları siler |
| `db:seed` | **HAYIR** | Ayarları/rolleri etkileyebilir; `plesk-post-deploy.sh` sadece ilk kurulum içindir |
| Dev `menu_one.sql` import | **HAYIR** | Canlı veriyi dev verisiyle değiştirir |
| `DROP DATABASE` | **HAYIR** | — |

**Deploy öncesi Plesk'te:** phpMyAdmin → `menu_one` → **Export** (yedek alın).

**Doğru deployment script:** `deploy/plesk-post-deploy-sql-import.sh` (SQL import modu — dosya import etmez, sadece kod günceller).

**Yanlış script:** `deploy/plesk-post-deploy.sh` → `db:seed` çalıştırır; **canlıda kullanmayın** (sadece boş ilk kurulum).

`APP_ENV=production` iken `migrate:fresh` ve `db:wipe` artık engellenir.

---

## Ticket sistemi + Türkçe karakter (canlı deploy)

### Ön koşul (.env)

```env
APP_LOCALE=tr
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
```

Plesk phpMyAdmin → veritabanı `menu_one` → **Karakter kümesi: utf8mb4_unicode_ci**

### Plesk Artisan sırası (ticket güncellemesi dahil)

Deployment script (`plesk-post-deploy-sql-import.sh`) zaten çoğunu yapar. Elle gerekiyorsa **sırayla**:

```
php artisan optimize:clear
php artisan deploy:check-production
php artisan deploy:prepare-production
php artisan db:ensure-utf8mb4
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan version:show
php artisan tenants:list
```

`deploy:prepare-production` içinde otomatik:
- `migrate --force` (ticket tabloları dahil)
- `db:ensure-utf8mb4` (charset onarımı)
- `TicketSeeder` (varsayılan kategoriler — mevcut veriyi silmez, `firstOrCreate`)
- `PlatformModules::syncPermissions()` (ticket modül izinleri)

### Türkçe `??` sorunu devam ederse

1. `php artisan db:ensure-utf8mb4 --dry-run` → hangi tablolar latin1?
2. `php artisan db:ensure-utf8mb4` → dönüştür
3. `php artisan optimize:clear` + `php artisan view:cache`
4. Plesk → PHP → **Restart** (OPcache)
5. Tarayıcıda hard refresh (Ctrl+F5)

**Not:** Eski latin1 ile kaydedilmiş metinler dönüşümden sonra düzelmeyebilir; o kayıtlar panelden yeniden kaydedilmeli.

### Ticket modülü — deploy sonrası kontrol

| Kontrol | Beklenen |
|---------|----------|
| `/platform/tickets` | Ticket listesi açılır |
| Platform menü | Ticket Yönetimi görünür (izin varsa) |
| `/profile?tab=ticket` | Müşteri ticket sekmesi |
| `ticket_categories` tablosu | En az 3 varsayılan kategori |

Platform admin değilse: Kullanıcı Grupları → `platform.tickets.view` / `edit` izinleri.

---

## Prod / local farkı (cafe session, cafe oluşturma)

**Semptom:** SQL'de tenant `619-718` ama panel `619` görüyor; cafe bağlanamıyor / oluşturulamıyor.

**Kök neden:** Eski kod tenant ID'yi integer sanıyor (`619-718` → `619`). **v2.0.62+** gerekli.

Deploy sonrası Plesk Artisan:

```
php artisan optimize:clear
php artisan config:cache
php artisan tenants:list
```

Tüm satırlar **OK** olmalı. `MISMATCH` varsa:

1. Menu-One.git → **Şimdi çek** → **Şimdi dağıt** (footer'da v2.0.62+)
2. Plesk → PHP 8.4 → **Restart** (OPcache eski kod tutuyor olabilir)
3. `tenants:list` tekrar

**Cafe oluşturma** ayrıca şunları ister:

- `license_types` tablosunda aktif kayıt (deploy script `tenants:repair-data` seed eder)
- Müşteri hesabında zaten cafe yok (`canCreateCafe`)
- Oturum: `SESSION_DRIVER=database` → deploy sonrası yeniden giriş gerekebilir
- Eski bug `users.tenant_id = 619` bıraktıysa: `php artisan tenants:repair-data` (619 → 619-718 düzeltir)
