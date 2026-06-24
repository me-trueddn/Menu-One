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
