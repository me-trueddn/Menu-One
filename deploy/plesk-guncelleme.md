# Menu-One — Plesk güncelleme (Git pull)

İlk kurulumda `vendor` ve `public/build` zip ile yüklendi — **bu normal**, Git bu dosyaları taşımaz.

Bundan sonra her güncelleme **otomatik deploy script** ile yapılmalı; elle zip gerekmez.

---

## Bir kez ayarlayın (Plesk Git)

**Git** → Repository Settings → **Ek deployment komutları**

`deploy/plesk-post-deploy-sql-import.sh` dosyasının **tüm içeriğini** yapıştırın.

| Ayar | Değer |
|------|--------|
| Deployment mode | **Automatic** (push/pull sonrası script çalışsın) |
| Deploy to | `/var/www/vhosts/trueddn.com.tr/panel.trueddn.com.tr` |

`.env` dosyasına dokunulmamalı (Plesk’te koruma varsa işaretleyin).

---

## Her kod güncellemesinde (sizin yapmanız gereken)

1. Bilgisayarda: `git push origin main`
2. Plesk: **Git → Pull** (veya automatic deploy bekleyin)

Script otomatik çalışır:
- `composer install --no-dev`
- vendor kontrolü (spatie, socialite)
- `deploy:prepare-production`
- `npm install --include=dev` + `npm run build`
- config / route / view cache

**Elle zip yüklemenize gerek yok.**

---

## Git pull sonrası kontrol (isteğe bağlı)

| Dosya | Olması gereken |
|-------|----------------|
| `vendor/spatie/laravel-permission` | Var |
| `public/build/manifest.json` | Var |

Yoksa deploy script hata vermiştir — Git ekranındaki **deploy log** çıktısına bakın.

---

## Deploy script çalışmazsa (manuel yedek)

Sırayla Plesk kutularında:

**Composer:**
```
install --no-dev --optimize-autoloader
```

**Node.js:**
```
install --include=dev
```
```
run build
```

**Artisan:**
```
config:clear
```
```
deploy:prepare-production
```
```
config:cache
```
```
route:cache
```
```
view:cache
```

`migrate` / `db:seed` **çalıştırmayın** (SQL import kullanıyorsanız).

---

## Ne zaman elle müdahale gerekir?

| Durum | Ne yapın |
|-------|----------|
| Normal kod güncellemesi | Sadece Git pull |
| `composer.lock` değişti | Pull yeterli (script composer çalıştırır) |
| `package.json` / CSS-JS değişti | Pull yeterli (script npm build çalıştırır) |
| Composer sunucuda hep bozuk | Bir kez `create-plesk-vendor-zip.ps1`, sonra script’i düzeltin |
| npm sunucuda hep bozuk | Bir kez PC’den `public/build` zip |
| Veritabanı şema değişikliği | phpMyAdmin yedek → Artisan: `migrate --force` (dikkatli) |

---

## Özet

```
İlk kurulum  → .env + SQL + (vendor zip) + (build zip)  ← elle, bir kez
Güncelleme   → git push + Plesk Git pull               ← otomatik script
```

Deploy script ayarlı değilse her pull sadece PHP dosyalarını günceller; `vendor` ve `build` eski kalır — bugün yaşadığınız sorun bu yüzden oldu.
