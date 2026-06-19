<?php

namespace App\Support;

class EmailTemplateRenderer
{
    public static function render(string $content, array $variables = []): string
    {
        $html = $content;

        foreach ($variables as $key => $value) {
            $html = str_replace('{'.$key.'}', e((string) $value), $html);
        }

        $replacements = [
            '/\[b\](.*?)\[\/b\]/s' => '<strong>$1</strong>',
            '/\[i\](.*?)\[\/i\]/s' => '<em>$1</em>',
            '/\[u\](.*?)\[\/u\]/s' => '<u>$1</u>',
            '/\[url=(.*?)\](.*?)\[\/url\]/s' => '<a href="$1">$2</a>',
            '/\[url\](.*?)\[\/url\]/s' => '<a href="$1">$1</a>',
        ];

        foreach ($replacements as $pattern => $replacement) {
            $html = preg_replace($pattern, $replacement, $html) ?? $html;
        }

        if (! preg_match('/<\s*(html|body|table|div|center|section|article)\b/i', $html)) {
            $html = nl2br($html);
        }

        return $html;
    }
}
