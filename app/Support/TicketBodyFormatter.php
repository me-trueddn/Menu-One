<?php

namespace App\Support;

class TicketBodyFormatter
{
    public static function normalize(string $body, string $format = 'html'): string
    {
        $body = trim($body);

        if ($body === '') {
            return '';
        }

        if ($format === 'bbcode') {
            $body = self::bbcodeToHtml($body);
        }

        return self::sanitizeHtml($body);
    }

    public static function sanitizeHtml(string $html): string
    {
        $allowed = '<p><br><strong><b><em><i><u><ul><ol><li><a><span><div>';

        $clean = strip_tags($html, $allowed);

        return preg_replace_callback(
            '/<a\s+([^>]*href\s*=\s*["\'])([^"\']*)(["\'][^>]*)>/i',
            function (array $matches) {
                $url = $matches[2];
                if (! preg_match('#^https?://#i', $url) && ! str_starts_with($url, 'mailto:')) {
                    return '<a href="#" rel="nofollow noopener">';
                }

                return '<a href="'.htmlspecialchars($url, ENT_QUOTES, 'UTF-8').'" rel="nofollow noopener" target="_blank">';
            },
            $clean,
        ) ?? $clean;
    }

    public static function bbcodeToHtml(string $text): string
    {
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

        $replacements = [
            '/\[b\](.*?)\[\/b\]/is' => '<strong>$1</strong>',
            '/\[i\](.*?)\[\/i\]/is' => '<em>$1</em>',
            '/\[u\](.*?)\[\/u\]/is' => '<u>$1</u>',
            '/\[url=(.*?)\](.*?)\[\/url\]/is' => '<a href="$1" rel="nofollow noopener" target="_blank">$2</a>',
            '/\[url\](.*?)\[\/url\]/is' => '<a href="$1" rel="nofollow noopener" target="_blank">$1</a>',
        ];

        foreach ($replacements as $pattern => $replacement) {
            $text = preg_replace($pattern, $replacement, $text) ?? $text;
        }

        return nl2br($text);
    }
}
