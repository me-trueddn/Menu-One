<?php

namespace App\Support;

class PasswordResetTemplate
{
    public static function subject(): string
    {
        return 'Parola Sıfırlama - {site_name}';
    }

    public static function body(): string
    {
        return <<<'HTML'
<div style="max-width:560px;margin:0 auto;font-family:'Segoe UI',Tahoma,Geneva,sans-serif;color:#1f2937;line-height:1.6">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;background:linear-gradient(135deg,#0f766e 0%,#115e59 100%);border-radius:12px 12px 0 0">
<tr>
<td style="padding:36px 32px;text-align:center">
<img src="{site_logo_url}" alt="{site_name}" style="max-height:48px;width:auto;display:block;margin:0 auto 16px">
<div style="font-size:26px;font-weight:700;color:#ffffff">Parola Sıfırlama</div>
</td>
</tr>
</table>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;background:#ffffff;border:1px solid #e5e7eb;border-top:none">
<tr>
<td style="padding:36px 32px">
<p style="margin:0 0 16px;font-size:16px">Merhaba [b]{name}[/b],</p>
<p style="margin:0 0 24px;font-size:15px;color:#4b5563">
Hesabınız için parola sıfırlama talebi aldık. Yeni parolanızı belirlemek için aşağıdaki butona tıklayın.
</p>
<table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto 28px">
<tr>
<td style="border-radius:8px;background:#0f766e">
<a href="{reset_url}" style="display:inline-block;padding:14px 36px;font-size:16px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:8px">Parolamı Sıfırla</a>
</td>
</tr>
</table>
<p style="margin:0 0 12px;font-size:14px;color:#6b7280">Buton çalışmıyorsa bağlantıyı kopyalayın:</p>
<p style="margin:0 0 24px;font-size:13px;word-break:break-all">[url={reset_url}]{reset_url}[/url]</p>
<p style="margin:0;font-size:13px;color:#9ca3af">Bu talebi siz yapmadıysanız bu e-postayı yok sayın. Bağlantı {expires_minutes} dakika geçerlidir.</p>
</td>
</tr>
</table>
</div>
HTML;
    }

    public static function subjectEn(): string
    {
        return 'Password Reset - {site_name}';
    }

    public static function bodyEn(): string
    {
        return <<<'HTML'
<div style="max-width:560px;margin:0 auto;font-family:'Segoe UI',Tahoma,Geneva,sans-serif;color:#1f2937;line-height:1.6">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;background:linear-gradient(135deg,#0f766e 0%,#115e59 100%);border-radius:12px 12px 0 0">
<tr>
<td style="padding:36px 32px;text-align:center">
<img src="{site_logo_url}" alt="{site_name}" style="max-height:48px;width:auto;display:block;margin:0 auto 16px">
<div style="font-size:26px;font-weight:700;color:#ffffff">Password Reset</div>
</td>
</tr>
</table>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;background:#ffffff;border:1px solid #e5e7eb;border-top:none">
<tr>
<td style="padding:36px 32px">
<p style="margin:0 0 16px;font-size:16px">Hello [b]{name}[/b],</p>
<p style="margin:0 0 24px;font-size:15px;color:#4b5563">
We received a password reset request for your account. Click the button below to choose a new password.
</p>
<table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto 28px">
<tr>
<td style="border-radius:8px;background:#0f766e">
<a href="{reset_url}" style="display:inline-block;padding:14px 36px;font-size:16px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:8px">Reset My Password</a>
</td>
</tr>
</table>
<p style="margin:0 0 12px;font-size:14px;color:#6b7280">If the button does not work, copy this link:</p>
<p style="margin:0 0 24px;font-size:13px;word-break:break-all">[url={reset_url}]{reset_url}[/url]</p>
<p style="margin:0;font-size:13px;color:#9ca3af">If you did not request this, ignore this email. The link expires in {expires_minutes} minutes.</p>
</td>
</tr>
</table>
</div>
HTML;
    }
}
