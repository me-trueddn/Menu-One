<?php

namespace App\Support;

class StaffInvitationTemplate
{
    public static function subject(): string
    {
        return '{cafe_name} ekibine davet - {site_name}';
    }

    public static function body(): string
    {
        return <<<'HTML'
<div style="max-width:560px;margin:0 auto;font-family:'Segoe UI',Tahoma,Geneva,sans-serif;color:#1f2937;line-height:1.6">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;background:linear-gradient(135deg,#7c3aed 0%,#5b21b6 100%);border-radius:12px 12px 0 0">
<tr>
<td style="padding:36px 32px;text-align:center">
<img src="{site_logo_url}" alt="{site_name}" style="max-height:48px;width:auto;display:block;margin:0 auto 16px">
<div style="font-size:26px;font-weight:700;color:#ffffff">Cafe Daveti</div>
</td>
</tr>
</table>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;background:#ffffff;border:1px solid #e5e7eb;border-top:none">
<tr>
<td style="padding:36px 32px">
<p style="margin:0 0 16px;font-size:16px">Merhaba [b]{name}[/b],</p>
<p style="margin:0 0 24px;font-size:15px;color:#4b5563">
[b]{invited_by}[/b] sizi [b]{cafe_name}[/b] ekibine [b]{role}[/b] rolüyle davet etti. Daveti görüntülemek ve kabul veya reddetmek için aşağıdaki bağlantıyı kullanın.
</p>
<table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto 28px">
<tr>
<td style="border-radius:8px;background:#7c3aed">
<a href="{invite_url}" style="display:inline-block;padding:14px 36px;font-size:16px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:8px">Daveti Görüntüle</a>
</td>
</tr>
</table>
<p style="margin:0 0 12px;font-size:14px;color:#6b7280">Buton çalışmıyorsa bağlantıyı kopyalayın:</p>
<p style="margin:0 0 24px;font-size:13px;word-break:break-all">[url={invite_url}]{invite_url}[/url]</p>
<p style="margin:0;font-size:13px;color:#9ca3af">Bu daveti siz beklemiyorsanız yok sayabilirsiniz. Bağlantı {expires_minutes} dakika geçerlidir. Linke tıklamak tek başına sizi cafeye eklemez; onay sayfasından kabul etmeniz gerekir.</p>
</td>
</tr>
</table>
</div>
HTML;
    }

    public static function subjectEn(): string
    {
        return 'Invitation to {cafe_name} - {site_name}';
    }

    public static function bodyEn(): string
    {
        return <<<'HTML'
<div style="max-width:560px;margin:0 auto;font-family:'Segoe UI',Tahoma,Geneva,sans-serif;color:#1f2937;line-height:1.6">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;background:linear-gradient(135deg,#7c3aed 0%,#5b21b6 100%);border-radius:12px 12px 0 0">
<tr>
<td style="padding:36px 32px;text-align:center">
<img src="{site_logo_url}" alt="{site_name}" style="max-height:48px;width:auto;display:block;margin:0 auto 16px">
<div style="font-size:26px;font-weight:700;color:#ffffff">Cafe Invitation</div>
</td>
</tr>
</table>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;background:#ffffff;border:1px solid #e5e7eb;border-top:none">
<tr>
<td style="padding:36px 32px">
<p style="margin:0 0 16px;font-size:16px">Hello [b]{name}[/b],</p>
<p style="margin:0 0 24px;font-size:15px;color:#4b5563">
[b]{invited_by}[/b] invited you to join [b]{cafe_name}[/b] as [b]{role}[/b]. Use the link below to review and accept or decline the invitation.
</p>
<table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto 28px">
<tr>
<td style="border-radius:8px;background:#7c3aed">
<a href="{invite_url}" style="display:inline-block;padding:14px 36px;font-size:16px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:8px">View Invitation</a>
</td>
</tr>
</table>
<p style="margin:0 0 12px;font-size:14px;color:#6b7280">If the button does not work, copy this link:</p>
<p style="margin:0 0 24px;font-size:13px;word-break:break-all">[url={invite_url}]{invite_url}[/url]</p>
<p style="margin:0;font-size:13px;color:#9ca3af">If you were not expecting this, you can ignore it. The link expires in {expires_minutes} minutes. Clicking the link alone does not add you to the cafe; you must confirm on the acceptance page.</p>
</td>
</tr>
</table>
</div>
HTML;
    }
}
