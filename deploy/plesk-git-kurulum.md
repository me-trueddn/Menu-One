# Menu-One — Plesk Git kurulumu (SSH yok)

Domain: **panel.trueddn.com.tr**  
Repo: **https://github.com/me-trueddn/Menu-One.git** (branch: `main`)

Subdomain yeni oluşturulduysa Git’i bu sırayla kurun. Olmazsa: `deploy/plesk-ui-kurulum.md` (zip upload).

---

## Ön koşullar

- Plesk **Git** eklentisi yüklü
- **Laravel** / **PHP Composer** / **Node.js** eklentileri aktif
- `menu_one` veritabanı + **menu_one.sql** import edilmiş
- **`migrate` / `db:seed` çalıştırılmamış** (SQL import kullanıyorsanız)

---

## 1) Hosting ayarı

**Websites & Domains** → **panel.trueddn.com.tr** → **Hosting Settings**

| Alan | Değer |
|------|--------|
| Document root | `panel.trueddn.com.tr/public` |

Kaydet.

---

## 2) `.env` dosyası (Git’ten ÖNCE)

Git `.env` içermez. Deploy’dan önce oluşturun:

**Files** → `panel.trueddn.com.tr` → **+ File** → `.env`

İçerik: `deploy/plesk-panel.env` şablonu + doldurulmuş alanlar:

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://panel.trueddn.com.tr
PANEL_URL=https://panel.trueddn.com.tr
APP_KEY=...          ← dev SQL import: local .env ile AYNI
DB_HOST=localhost
DB_DATABASE=menu_one
DB_USERNAME=...
DB_PASSWORD='...'    ← $ varsa tek tırnak
```

---

## 3) Git deposu ekle

**Websites & Domains** → **panel.trueddn.com.tr** → **Git** → **Add Repository**

### Bağlantı (HTTPS — en kolay, SSH gerekmez)

| Alan | Değer |
|------|--------|
| Remote URL | `https://github.com/me-trueddn/Menu-One.git` |
| Branch | `main` |

GitHub **Personal Access Token** istenirse:
- GitHub → Settings → Developer settings → Personal access tokens
- Repo read yetkisi yeterli
- Plesk’te şifre alanına token yapıştırın

### SSH alternatifi

URL: `git@github.com:me-trueddn/Menu-One.git`  
Plesk Git ekranındaki **public key**’i GitHub → Repo → Settings → Deploy keys → Add.

### Dağıtım (Deployment)

| Alan | Değer |
|------|--------|
| Deployment mode | Automatic veya Manual |
| Deploy to / Server path | `/var/www/vhosts/trueddn.com.tr/panel.trueddn.com.tr` |

**Önemli:** `git/laravel_xxx` yolunu elle yazmayın. Plesk bunu kendi `git/` klasöründe tutar; siz sadece **hedef site dizinini** seçin.

`.env` dosyasının üzerine yazılmasın (Plesk’te “preserve .env” benzeri seçenek varsa işaretleyin).

---

## 4) Ek deployment komutları

Git → Repository Settings → **Additional deployment actions** (veya Dağıtım scripti)

**SQL import ettiyseniz** — `deploy/plesk-post-deploy-sql-import.sh` içeriğini yapıştırın.

**Boş DB + migrate istiyorsanız** — `deploy/plesk-post-deploy.sh` kullanın.

---

## 5) İlk deploy

Git ekranında **Pull** veya **Deploy**.

Başarılı olunca `panel.trueddn.com.tr` altında `artisan`, `app/`, `public/` görünmeli.

---

## 6) Manuel kontrol (deploy script yetmezse)

### Composer kutusu
```
install --no-dev --optimize-autoloader
```

### Artisan (başına `php artisan` yazmayın)
```
config:clear
deploy:prepare-production
storage:link --force
config:cache
route:cache
view:cache
```

### Node.js
```
ci
run build
```

---

## 7) Test

https://panel.trueddn.com.tr — dev SQL’deki kullanıcı ile giriş.

---

## Sık hatalar

| Hata | Çözüm |
|------|--------|
| `filemng: git/laravel_xxx` | Git kaydı bozuk → subdomain sil/yenile veya zip upload |
| `Command "php" is not defined` | Artisan kutusuna sadece `config:clear` yazın |
| `deploy` namespace yok | Eski kod; Git pull / main branch kontrol |
| `users already exists` | SQL import sonrası migrate çalıştırmayın |
| `.env` kayboldu | Git deploy öncesi .env oluşturun; tekrar yapıştırın |
| Vite 500 | `npm run build` |

---

## Git olmazsa

1. `deploy/create-plesk-zip.ps1` → zip oluştur
2. `deploy/plesk-ui-kurulum.md` adımları
