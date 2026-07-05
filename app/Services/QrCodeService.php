<?php

namespace App\Services;

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Common\Version;
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class QrCodeService
{
    public function svgDataUri(string $content, int $scale = 6): string
    {
        $svg = $this->renderSvg($content, $scale);

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    public function renderSvg(string $content, int $scale = 6): string
    {
        $renderer = new QRCode($this->options(QRMarkupSVG::class, $scale));
        $svg = $renderer->render($content);

        if (! is_string($svg)) {
            throw new \RuntimeException('QR code renderer returned a non-string SVG payload.');
        }

        return $svg;
    }

    public function renderPng(string $content, int $scale = 6): string
    {
        if (! extension_loaded('gd')) {
            throw new \RuntimeException('GD extension is required for PNG QR output.');
        }

        $renderer = new QRCode($this->options(QRGdImagePNG::class, $scale));
        $png = $renderer->render($content);

        if (! is_string($png)) {
            throw new \RuntimeException('QR code renderer returned a non-string PNG payload.');
        }

        return $png;
    }

    /**
     * @return array{content: string, mime: string, extension: string}
     */
    public function downloadable(string $content, int $scale = 6): array
    {
        if (extension_loaded('gd')) {
            return [
                'content' => $this->renderPng($content, $scale),
                'mime' => 'image/png',
                'extension' => 'png',
            ];
        }

        return [
            'content' => $this->renderSvg($content, $scale),
            'mime' => 'image/svg+xml',
            'extension' => 'svg',
        ];
    }

    /**
     * @param  class-string  $outputInterface
     */
    private function options(string $outputInterface, int $scale): QROptions
    {
        return new QROptions([
            'version' => Version::AUTO,
            'outputInterface' => $outputInterface,
            'eccLevel' => EccLevel::L,
            'scale' => max(1, $scale),
            'outputBase64' => false,
        ]);
    }
}
