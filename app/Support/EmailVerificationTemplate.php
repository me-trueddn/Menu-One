<?php

namespace App\Support;

class EmailVerificationTemplate
{
    public static function subject(): string
    {
        return 'E-posta Doğrulama - {site_name}';
    }

    public static function body(): string
    {
        return <<<'HTML'
<div style="max-width:560px;margin:0 auto;font-family:'Segoe UI',Tahoma,Geneva,sans-serif;color:#1f2937;line-height:1.6">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;background:linear-gradient(135deg,#2563eb 0%,#1e40af 100%);border-radius:12px 12px 0 0">
<tr>
<td style="padding:36px 32px;text-align:center">
<div style="font-size:13px;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,0.85);margin-bottom:8px">{site_name}</div>
<div style="font-size:26px;font-weight:700;color:#ffffff">E-posta Doğrulama</div>
</td>
</tr>
</table>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;background:#ffffff;border:1px solid #e5e7eb;border-top:none">
<tr>
<td style="padding:36px 32px">
<p style="margin:0 0 16px;font-size:16px">Merhaba [b]{name}[/b],</p>
<p style="margin:0 0 24px;font-size:15px;color:#4b5563">
{site_name} hesabınızı oluşturduğunuz için teşekkür ederiz. Kaydınızı tamamlamak ve panele erişmek için e-posta adresinizi doğrulamanız gerekmektedir.
</p>
<table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto 28px">
<tr>
<td style="border-radius:8px;background:#2563eb">
<a href="{verify_url}" style="display:inline-block;padding:14px 36px;font-size:16px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:8px">E-posta Adresimi Doğrula</a>
</td>
</tr>
</table>
<p style="margin:0 0 12px;font-size:14px;color:#6b7280">
Buton çalışmıyorsa aşağıdaki bağlantıyı tarayıcınıza kopyalayın:
</p>
<p style="margin:0 0 24px;font-size:13px;word-break:break-all">
[url={verify_url}]{verify_url}[/url]
</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;background:#f9fafb;border-radius:8px;border:1px solid #e5e7eb">
<tr>
<td style="padding:16px 20px">
<p style="margin:0 0 6px;font-size:13px;color:#374151"><strong>Hesap:</strong> {email}</p>
<p style="margin:0;font-size:13px;color:#374151"><strong>Geçerlilik süresi:</strong> {expires_minutes} dakika</p>
</td>
</tr>
</table>
<p style="margin:24px 0 0;font-size:13px;color:#9ca3af">
Bu işlemi siz yapmadıysanız bu e-postayı yok sayabilirsiniz. Hesabınız doğrulanmadan giriş yapılamaz.
</p>
</td>
</tr>
</table>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse">
<tr>
<td style="padding:20px 32px;text-align:center;font-size:12px;color:#9ca3af">
© {site_name} · Güvenli doğrulama bağlantısı
</td>
</tr>
</table>
</div>
HTML;
    }

    public static function subjectEn(): string
    {
        return 'Email Verification - {site_name}';
    }

    public static function bodyEn(): string
    {
        return <<<'HTML'
<div style="max-width:560px;margin:0 auto;font-family:'Segoe UI',Tahoma,Geneva,sans-serif;color:#1f2937;line-height:1.6">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;background:linear-gradient(135deg,#2563eb 0%,#1e40af 100%);border-radius:12px 12px 0 0">
<tr>
<td style="padding:36px 32px;text-align:center">
<div style="font-size:13px;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,0.85);margin-bottom:8px">{site_name}</div>
<div style="font-size:26px;font-weight:700;color:#ffffff">Email Verification</div>
</td>
</tr>
</table>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;background:#ffffff;border:1px solid #e5e7eb;border-top:none">
<tr>
<td style="padding:36px 32px">
<p style="margin:0 0 16px;font-size:16px">Hello [b]{name}[/b],</p>
<p style="margin:0 0 24px;font-size:15px;color:#4b5563">
Thank you for creating your {site_name} account. Please verify your email address to complete registration and access the panel.
</p>
<table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto 28px">
<tr>
<td style="border-radius:8px;background:#2563eb">
<a href="{verify_url}" style="display:inline-block;padding:14px 36px;font-size:16px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:8px">Verify My Email</a>
</td>
</tr>
</table>
<p style="margin:0 0 12px;font-size:14px;color:#6b7280">
If the button does not work, copy and paste this link into your browser:
</p>
<p style="margin:0 0 24px;font-size:13px;word-break:break-all">
[url={verify_url}]{verify_url}[/url]
</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;background:#f9fafb;border-radius:8px;border:1px solid #e5e7eb">
<tr>
<td style="padding:16px 20px">
<p style="margin:0 0 6px;font-size:13px;color:#374151"><strong>Account:</strong> {email}</p>
<p style="margin:0;font-size:13px;color:#374151"><strong>Expires in:</strong> {expires_minutes} minutes</p>
</td>
</tr>
</table>
<p style="margin:24px 0 0;font-size:13px;color:#9ca3af">
If you did not create this account, you can safely ignore this email. You cannot sign in until your email is verified.
</p>
</td>
</tr>
</table>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse">
<tr>
<td style="padding:20px 32px;text-align:center;font-size:12px;color:#9ca3af">
© {site_name} · Secure verification link
</td>
</tr>
</table>
</div>
HTML;
    }
}
