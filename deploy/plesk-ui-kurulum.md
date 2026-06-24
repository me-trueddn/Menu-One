# Menu-One — Plesk kurulumu (SSH yok, zip upload — Git yedek plan)

Git ile denemek için önce: **deploy/plesk-git-kurulum.md**

Domain: **panel.trueddn.com.tr**

Bu rehber yalnızca Plesk arayüzü, Dosya Yöneticisi, phpMyAdmin, Composer, Artisan ve Node.js kullanır.

---

## 0) Bozuk Git’i görmezden gelin

`git/laravel_xxx` hatası Plesk Git kaydından gelir. **Git menüsüne girmeyin.**

İsterseniz subdomain’i sıfırlayın (Git kaydı da silinir):

1. **Websites & Domains** → **panel.trueddn.com.tr** → **Remove** / **Kaldır**
2. **Add Subdomain** → `panel` → `trueddn.com.tr`
3. Aşağıdaki adımlardan devam edin

Subdomain’i silmek istemezseniz sadece dosya klasörünü temizleyip zip yükleyin.

---

## 1) Veritabanı

1. **Databases** → **Add Database**
   - Ad: `menu_one`
   - Kullanıcı oluştur / bağla (ör. `uyg_mo_prod`)
2. **phpMyAdmin** → `menu_one` → **Import**
   - Dosya: bilgisayarınızdaki `menu_one.sql` (dev dump)
3. Import sonrası `SHOW TABLES;` — tablolar listelenmeli

**Not:** SQL import ettiyseniz `migrate`, `db:seed`, `migrate:fresh` **çalıştırmayın**.

---

## 2) Kurulum zip’i (bilgisayarınızda)

Proje klasöründe PowerShell:

```powershell
cd C:\Users\faltun\Desktop\Menu-One
.\deploy\create-plesk-zip.ps1
```

Oluşan dosya: `Menu-One-plesk.zip` (masaüstünde veya proje kökünde)

Zip içinde **yok:** `vendor`, `node_modules`, `.env`, `.git`

---

## 3) Dosyaları Plesk’e yükle

1. **Files** → `/var/www/vhosts/trueddn.com.tr/panel.trueddn.com.tr`
2. İçindeki **eski dosyaları silin** (`.env` varsa önce indirip yedekleyin)
3. `Menu-One-plesk.zip` yükleyin → **Extract** / **Çıkart**
4. Zip içinde tek klasör varsa (`Menu-One/`), içindekileri bir üst dizine taşıyın; `artisan` dosyası doğrudan `panel.trueddn.com.tr/artisan` yolunda olmalı

---

## 4) Document root

**Hosting Settings** → **Document root**:

```
panel.trueddn.com.tr/public
```

(`public` klasörü web kökü olmalı.)

---

## 5) `.env` dosyası

**Files** → `panel.trueddn.com.tr` → `.env` oluştur veya düzenle.

Şablon: `deploy/plesk-panel.env`

Doldurulması gerekenler:

| Anahtar | Örnek |
|---------|--------|
| `APP_KEY` | Dev SQL import ettiyseniz **local `.env` ile aynı** `APP_KEY` |
| `DB_USERNAME` | Plesk DB kullanıcısı |
| `DB_PASSWORD` | Şifre (`$` varsa tek tırnak: `'sifre$123'`) |
| `SUPER_ADMIN_PASSWORD` | İlk giriş (sadece seed kullanırsanız) |

SQL import kullandıysanız giriş **dev’deki kullanıcı/şifre** ile olur.

---

## 6) Composer

**Laravel** veya **PHP Composer** kutusu — sadece:

```
install --no-dev --optimize-autoloader
```

(`composer` yazmayın.)

---

## 7) Artisan

Kutuda zaten `php artisan` yazar. Siz **sadece** komutu yazın (her biri ayrı):

```
config:clear
```

```
deploy:prepare-production
```

(`deploy:prepare-production` zip’te v2.0.48+ yoksa aşağıdaki SQL’i phpMyAdmin’de çalıştırın.)

```sql
UPDATE settings SET value = 'https://panel.trueddn.com.tr' WHERE `key` = 'panel_url';
TRUNCATE TABLE cache;
TRUNCATE TABLE cache_locks;
TRUNCATE TABLE sessions;
TRUNCATE TABLE user_login_tokens;
```

```
cache:clear
```

```
storage:link --force
```

---

## 8) Node.js (Vite build)

**Node.js** kutusu:

```
ci
```

```
run build
```

(Plesk sürümüne göre `npm ci` / `npm run build` de olabilir.)

`public/build/manifest.json` oluşmalı.

---

## 9) Cache

Artisan:

```
config:cache
```

```
route:cache
```

```
view:cache
```

---

## 10) İzinler

**Files** → `storage` ve `bootstrap/cache` → **Permissions** → owner+group yazılabilir, diğerleri kapalı (`770` / `ug+rwx,o-rwx`).

---

## 11) Test

- https://panel.trueddn.com.tr
- Dev SQL import → dev kullanıcı ile giriş

---

## Sık hatalar

| Hata | Çözüm |
|------|--------|
| `Command "php" is not defined` | Artisan kutusuna `php artisan` yazmayın |
| `users already exists` | SQL import sonrası `migrate` çalıştırmayın |
| `Vite manifest not found` | `npm run build` |
| Mail/captcha çalışmıyor | `.env` `APP_KEY` dev ile aynı olmalı veya panelden ayarları yeniden kaydedin |
| `git/laravel_xxx` | Git kullanmayın; zip ile kurun |

---

## Güncelleme (sonraki sürümler)

1. Bilgisayarda yeni zip oluştur (`create-plesk-zip.ps1`)
2. Plesk’te dosyaları güncelle (`.env` silmeyin)
3. Composer: `install --no-dev --optimize-autoloader`
4. Artisan: `config:clear`, `deploy:prepare-production`, cache komutları
5. Node: `ci`, `run build`
