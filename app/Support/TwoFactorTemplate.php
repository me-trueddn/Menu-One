<?php

namespace App\Support;

class TwoFactorTemplate
{
    public static function enabledSubject(): string
    {
        return '2FA Etkinleştirildi - {site_name}';
    }

    public static function enabledBody(): string
    {
        return <<<'HTML'
<div style="max-width:560px;margin:0 auto;font-family:'Segoe UI',Tahoma,Geneva,sans-serif;color:#1f2937;line-height:1.6">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;background:linear-gradient(135deg,#16a34a 0%,#15803d 100%);border-radius:12px 12px 0 0">
<tr><td style="padding:32px;text-align:center">
<img src="{site_logo_url}" alt="{site_name}" style="max-height:48px;width:auto;display:block;margin:0 auto 16px">
<div style="color:#fff;font-size:24px;font-weight:700">2FA Etkin</div>
</td></tr>
</table>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;background:#fff;border:1px solid #e5e7eb;border-top:none">
<tr><td style="padding:32px">
<p style="margin:0 0 16px">Merhaba [b]{name}[/b],</p>
<p style="margin:0 0 16px;color:#4b5563">{site_name} hesabınızda iki faktörlü doğrulama (2FA) [b]etkinleştirildi[/b].</p>
<p style="margin:0;font-size:13px;color:#9ca3af">Bu işlemi siz yapmadıysanız derhal destek ile iletişime geçin.</p>
</td></tr>
</table>
</div>
HTML;
    }

    public static function disabledSubject(): string
    {
        return '2FA Devre Dışı - {site_name}';
    }

    public static function disabledBody(): string
    {
        return <<<'HTML'
<div style="max-width:560px;margin:0 auto;font-family:'Segoe UI',Tahoma,Geneva,sans-serif;color:#1f2937;line-height:1.6">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;background:linear-gradient(135deg,#dc2626 0%,#b91c1c 100%);border-radius:12px 12px 0 0">
<tr><td style="padding:32px;text-align:center">
<img src="{site_logo_url}" alt="{site_name}" style="max-height:48px;width:auto;display:block;margin:0 auto 16px">
<div style="color:#fff;font-size:24px;font-weight:700">2FA Kapatıldı</div>
</td></tr>
</table>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;background:#fff;border:1px solid #e5e7eb;border-top:none">
<tr><td style="padding:32px">
<p style="margin:0 0 16px">Merhaba [b]{name}[/b],</p>
<p style="margin:0 0 16px;color:#4b5563">{site_name} hesabınızda iki faktörlü doğrulama (2FA) [b]devre dışı bırakıldı[/b].</p>
<p style="margin:0;font-size:13px;color:#9ca3af">Bu işlemi siz yapmadıysanız derhal destek ile iletişime geçin ve parolanızı değiştirin.</p>
</td></tr>
</table>
</div>
HTML;
    }

    public static function enabledSubjectEn(): string
    {
        return '2FA Enabled - {site_name}';
    }

    public static function enabledBodyEn(): string
    {
        return <<<'HTML'
<div style="max-width:560px;margin:0 auto;font-family:'Segoe UI',Tahoma,Geneva,sans-serif;color:#1f2937;line-height:1.6">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;background:linear-gradient(135deg,#16a34a 0%,#15803d 100%);border-radius:12px 12px 0 0">
<tr><td style="padding:32px;text-align:center">
<img src="{site_logo_url}" alt="{site_name}" style="max-height:48px;width:auto;display:block;margin:0 auto 16px">
<div style="color:#fff;font-size:24px;font-weight:700">2FA Enabled</div>
</td></tr>
</table>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;background:#fff;border:1px solid #e5e7eb;border-top:none">
<tr><td style="padding:32px">
<p style="margin:0 0 16px">Hello [b]{name}[/b],</p>
<p style="margin:0 0 16px;color:#4b5563">Two-factor authentication (2FA) was [b]enabled[/b] on your {site_name} account.</p>
<p style="margin:0;font-size:13px;color:#9ca3af">If you did not make this change, contact support immediately.</p>
</td></tr>
</table>
</div>
HTML;
    }

    public static function disabledSubjectEn(): string
    {
        return '2FA Disabled - {site_name}';
    }

    public static function disabledBodyEn(): string
    {
        return <<<'HTML'
<div style="max-width:560px;margin:0 auto;font-family:'Segoe UI',Tahoma,Geneva,sans-serif;color:#1f2937;line-height:1.6">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;background:linear-gradient(135deg,#dc2626 0%,#b91c1c 100%);border-radius:12px 12px 0 0">
<tr><td style="padding:32px;text-align:center">
<img src="{site_logo_url}" alt="{site_name}" style="max-height:48px;width:auto;display:block;margin:0 auto 16px">
<div style="color:#fff;font-size:24px;font-weight:700">2FA Disabled</div>
</td></tr>
</table>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;background:#fff;border:1px solid #e5e7eb;border-top:none">
<tr><td style="padding:32px">
<p style="margin:0 0 16px">Hello [b]{name}[/b],</p>
<p style="margin:0 0 16px;color:#4b5563">Two-factor authentication (2FA) was [b]disabled[/b] on your {site_name} account.</p>
<p style="margin:0;font-size:13px;color:#9ca3af">If you did not make this change, contact support and change your password immediately.</p>
</td></tr>
</table>
</div>
HTML;
    }
}
