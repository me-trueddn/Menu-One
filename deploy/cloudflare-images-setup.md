# Cloudflare Images + Stream Kurulumu

Menu-One müşteri yüklemelerini (ürün görseli, cafe logosu, site görseli, ürün videosu) Cloudflare üzerinde barındırabilir. Ayarlar: **Platform → Site yönetimi → Cloudflare Entegrasyonu**.

## 1. Cloudflare Dashboard

1. **Images** ürününü etkinleştirin.
2. **Stream** ürününü etkinleştirin (ürün videosu için).
3. **Account ID**: Dashboard sağ sütun veya Overview.
4. **Account hash**: Images → Developer Resources → `imagedelivery.net/<hash>/...`

## 2. Sabit variant isimleri (zorunlu)

Maliyet kontrolü için uygulama yalnızca şu variant isimlerini kullanır. Cloudflare Dashboard → **Images → Variants** altında **aynı isimlerle** oluşturun:

| Variant | Önerilen ayar | Kullanım |
|---------|----------------|----------|
| `public` | Genel görüntüleme | Site logosu, SEO OG |
| `product` | ~600×600, fit=cover | Menü ürün kartları |
| `logo` | ~256×256, fit=contain | Cafe logoları |

Dinamik `width=` / `height=` URL parametresi **kullanılmaz**.

## 3. API Token

**My Profile → API Tokens → Create Token**

Önerilen izinler:

- Account → **Cloudflare Images** → Edit
- Account → **Cloudflare Stream** → Edit

Token değerini panelde **Cloudflare API Token** alanına kaydedin (şifreli saklanır).

## 4. Panel ayarları

| Alan | Açıklama |
|------|----------|
| Cloudflare Images aktif | Müşteri görselleri CF Images'e gider |
| Cloudflare Stream aktif | Ürün MP4 yüklemeleri Stream'e gider |
| Account ID | CF hesap kimliği |
| Account hash | `imagedelivery.net` yolu |
| API Token | Images + Stream yetkili token |
| Stream müşteri subdomain | Opsiyonel; boşsa `customer-videodelivery.net` |

## 5. Yükleme limitleri (uygulama)

### Görseller (sunucuda ön-işleme sonrası CF'ye)

| Bağlam | Max boyut | Max piksel | Format |
|--------|-----------|------------|--------|
| Ürün | 1 MB | 1024×1024 | JPEG, PNG, WebP |
| Logo | 512 KB | 512×512 | JPEG, PNG, WebP |
| Site | 1 MB | 1200×400 | JPEG, PNG, WebP |

GIF, SVG, ICO yerel diskte kalır (favicon vb.).

### Video

- MP4, max 15 MB, max 30 saniye (sunucuda `ffprobe` varsa süre kontrolü)

## 6. Teşhis

```bash
php artisan cloudflare:diagnose
```

## 7. Geriye uyumluluk

- Kapalıyken davranış değişmez (`storage/app/public`).
- Veritabanındaki mevcut `images/...` yolları çalışmaya devam eder.
- Yeni CF referansları: `cfi:{image_id}`, `cfs:{stream_uid}`.

## 8. Üretim notları

- Sunucuda **PHP GD** eklentisi gerekir.
- `php artisan config:cache` sonrası ayarlar DB'den okunur.
- Mevcut local medyayı CF'ye taşımak için ayrı migrasyon komutu planlanmıştır (Phase 2).
