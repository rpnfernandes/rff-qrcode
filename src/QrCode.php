<?php declare(strict_types=1);

namespace RFF\QrCode;

/**
 * Convenient shorthand facade for QrCodeGenerator.
 *
 * Allows concise calls such as:
 * - `QrCode::svg('https://example.com')`
 * - `QrCode::svg('https://example.com', width: 300, height: 200)`
 * - `QrCode::dataUri('https://example.com', width: 300)`
 * - `QrCode::img('https://example.com', width: 250, height: 180)`
 */
class QrCode extends QrCodeGenerator
{
    /**
     * Shorthand to render SVG string.
     */
    public static function svg(
        string $text,
        int $width = 200,
        ?int $height = null,
        string $fg = '#000000',
        string $bg = '#ffffff',
        int $margin = 4,
        int $ecl = self::ECL_M,
        ?int $size = null
    ): string {
        return self::renderSvg($text, $width, $height, $fg, $bg, $margin, $ecl, $size);
    }

    /**
     * Shorthand to render Base64 Data URI.
     */
    public static function dataUri(
        string $text,
        int $width = 200,
        ?int $height = null,
        string $fg = '#000000',
        string $bg = '#ffffff',
        int $margin = 4,
        int $ecl = self::ECL_M,
        ?int $size = null
    ): string {
        return self::renderDataUriSvg($text, $width, $height, $fg, $bg, $margin, $ecl, $size);
    }

    /**
     * Shorthand to render HTML <img> tag with embedded Data URI.
     */
    public static function img(
        string $text,
        int $width = 200,
        ?int $height = null,
        string $alt = 'QR Code',
        string $fg = '#000000',
        string $bg = '#ffffff',
        int $margin = 4,
        int $ecl = self::ECL_M,
        ?int $size = null
    ): string {
        return self::renderHtmlImg($text, $width, $height, $alt, $fg, $bg, $margin, $ecl, $size);
    }

    /**
     * Shorthand to generate 2D boolean module matrix.
     */
    public static function matrix(string $text, int $ecl = self::ECL_M): array
    {
        return self::generate($text, $ecl);
    }
}
