<div align="center">

# RFF QR Code

**A lightweight, zero-dependency, pure PHP QR Code Generator compliant with ISO/IEC 18004 (Versions 1 to 40).**

[![Latest Version](https://img.shields.io/badge/version-2.3.0-blue.svg?style=flat-square)](https://github.com/rfernandes/rff-qrcode)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.0-8892BF.svg?style=flat-square)](https://php.net)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=flat-square)](LICENSE)
[![Zero Dependencies](https://img.shields.io/badge/dependencies-0-success.svg?style=flat-square)](#highlights)
[![PSR-4 Compliant](https://img.shields.io/badge/PSR--4-compliant-brightgreen.svg?style=flat-square)](#installation)

Generate high-performance, infinite-resolution vector **SVG** and **Base64 Data URI** QR codes with 100% native PHP — no GD, no ImageMagick, and zero third-party packages required.

</div>

---

## Highlights

- **Zero Dependencies**: 100% pure PHP. No `ext-gd`, `ext-imagick`, or external Composer runtime dependencies.
- **Full Model 2 Support (Versions 1 to 40)**: Scales seamlessly from $21 \times 21$ modules (Version 1) up to $177 \times 177$ modules (Version 40, up to 2,953 bytes).
- **Custom Width & Height**: Supports independent width and height rendering, custom sizes, and quiet zone margins.
- **Galois Field GF(256) ECC**: Native Reed-Solomon error correction calculations across all levels (L, M, Q, H).
- **8-Mask Evaluation**: Implements penalty scoring to pick the optimal pattern mask automatically.
- **Infinite Resolution**: Renders clean, standalone vector SVG strings and ready-to-embed Data URIs.
- **Modern Architecture**: PSR-4 compliant, typed with `declare(strict_types=1)`, compatible with PHP 8.0 through 8.4+.
- **Backwards Compatible**: Includes an ergonomic `QrCode` facade and legacy fallback support.

---

## Requirements

- **PHP 8.0** or higher (PHP 8.0, 8.1, 8.2, 8.3, 8.4+)

---

## Installation

Install the package via [Composer](https://getcomposer.org/):

```bash
composer require rff/qrcode
```

---

## Quick Start

### Using the Shorthand Facade (`QrCode`)

```php
use RFF\QrCode\QrCode;

// 1. Output a direct SVG string (default 200x200)
$svg = QrCode::svg('https://example.com');

// 2. Output with custom width and height
$svg = QrCode::svg('https://example.com', width: 360, height: 240);

// 3. Output a Base64 Data URI (e.g. for <img src="...">)
$dataUri = QrCode::dataUri('https://example.com', width: 300, height: 300);

// 4. Render a complete HTML <img> element
echo QrCode::img('https://example.com', width: 250, height: 180, alt: 'Scan Me');
```

### Using `QrCodeGenerator`

```php
use RFF\QrCode\QrCodeGenerator;

// Generate SVG with custom dimensions
$svg = QrCodeGenerator::renderSvg('https://example.com', width: 400, height: 250);

// Generate Data URI
$dataUri = QrCodeGenerator::renderDataUriSvg('https://example.com');
```

---

## Usage Examples

### 1. Independent Width and Height

You can specify custom dimensions for both width and height independently:

```php
use RFF\QrCode\QrCode;

// Custom rectangle bounding box (360px wide, 240px high)
$svg = QrCode::svg(
    text: 'https://example.com',
    width: 360,
    height: 240
);
```

> **Note**: For backward compatibility, passing a single numeric size `$size` or named parameter `size: 300` automatically sets both width and height equally.

### 2. Custom Colors, Margins, and Quiet Zone

```php
use RFF\QrCode\QrCode;

$svg = QrCode::svg(
    text: 'https://github.com/rfernandes/rff-qrcode',
    width: 320,
    height: 320,
    fg: '#1e3a8a',           // Foreground module color
    bg: '#f8fafc',           // Background color
    margin: 4,               // Quiet zone margin in modules
    ecl: QrCode::ECL_M       // Error Correction Level
);
```

### 3. Transparent Background

Set `bg` to `'transparent'` to omit the background rectangle:

```php
$svg = QrCode::svg(
    text: 'https://example.com',
    width: 250,
    height: 250,
    fg: '#0f172a',
    bg: 'transparent'
);
```

### 4. Inline in HTML Pages

```php
use RFF\QrCode\QrCode;

$imgTag = QrCode::img(
    text: 'https://example.com',
    width: 280,
    height: 280,
    alt: 'Payment QR Code'
);

echo $imgTag;
// Outputs: <img src="data:image/svg+xml;base64,..." width="280" height="280" alt="Payment QR Code" />
```

### 5. Error Correction Levels

Choose the appropriate error correction level depending on your use case:

```php
use RFF\QrCode\QrCode;

// Levels available: ECL_L, ECL_M, ECL_Q, ECL_H
$svg = QrCode::svg(
    text: 'Critical Data',
    ecl: QrCode::ECL_H // Recovers up to ~30% damaged or obscured data
);
```

| Constant | Level | Approx. Data Recovery | Best For |
|---|---|---|---|
| `QrCode::ECL_L` | **Low** | ~7% | Maximum storage capacity (clean digital screens) |
| `QrCode::ECL_M` | **Medium** | ~15% | Standard everyday use (Default) |
| `QrCode::ECL_Q` | **Quartile** | ~25% | Printed materials subject to minor smudging |
| `QrCode::ECL_H` | **High** | ~30% | Harsh physical environments or center logos |

### 6. Full ISO Versions 1 through 40 Support

The library automatically selects the smallest QR version that fits your data:

- **Version 1**: $21 \times 21$ modules (up to 19 data bytes)
- **Version 10**: $57 \times 57$ modules (up to 271 data bytes)
- **Version 20**: $97 \times 97$ modules (up to 858 data bytes)
- **Version 40**: $177 \times 177$ modules (up to 2,953 data bytes)

```php
use RFF\QrCode\QrCode;

// Large data payloads automatically scale up to Version 40
$longText = str_repeat('Extensive structured payload data. ', 50); // ~1,750 bytes
$svg = QrCode::svg($longText);
```

### 7. Raw Boolean Matrix (Custom Renderers / CLI / Canvas)

You can retrieve the raw 2D boolean array representing the module grid (`true` = dark module, `false` = light module):

```php
use RFF\QrCode\QrCode;

$matrix = QrCode::matrix('ASCII QR');

foreach ($matrix as $row) {
    foreach ($row as $isDark) {
        echo $isDark ? '██' : '  ';
    }
    echo PHP_EOL;
}
```

---

## API Reference

### `RFF\QrCode\QrCode` (Facade)

| Method | Return Type | Description |
|---|---|---|
| `QrCode::svg(string $text, int $width = 200, ?int $height = null, string $fg = '#000000', string $bg = '#ffffff', int $margin = 4, int $ecl = QrCode::ECL_M, ?int $size = null)` | `string` | Returns standalone vector SVG markup |
| `QrCode::dataUri(string $text, int $width = 200, ?int $height = null, string $fg = '#000000', string $bg = '#ffffff', int $margin = 4, int $ecl = QrCode::ECL_M, ?int $size = null)` | `string` | Returns Base64 SVG Data URI |
| `QrCode::img(string $text, int $width = 200, ?int $height = null, string $alt = 'QR Code', string $fg = '#000000', string $bg = '#ffffff', int $margin = 4, int $ecl = QrCode::ECL_M, ?int $size = null)` | `string` | Returns HTML `<img>` tag |
| `QrCode::matrix(string $text, int $ecl = QrCode::ECL_M)` | `array<int, array<int, bool>>` | Returns raw 2D boolean module matrix |

### `RFF\QrCode\QrCodeGenerator` (Engine)

| Method | Return Type | Description |
|---|---|---|
| `QrCodeGenerator::generate(string $text, int $ecl = self::ECL_M)` | `array<int, array<int, bool>>` | Encodes data (Versions 1-40) and returns module matrix |
| `QrCodeGenerator::renderSvg(...)` | `string` | Renders direct SVG string with width and height |
| `QrCodeGenerator::renderDataUriSvg(...)` | `string` | Renders Base64 Data URI with width and height |
| `QrCodeGenerator::renderHtmlImg(...)` | `string` | Renders complete HTML `<img>` tag with width and height |

---

## Testing

Run tests with PHPUnit:

```bash
composer test
# or
./vendor/bin/phpunit
```

Or run the bundled zero-dependency standalone test suite directly:

```bash
php tests/run_tests.php
```

---

## Interactive Demo

You can test and preview the generator immediately via CLI:

```bash
php examples/demo.php
```

Or run a local web server to view interactive browser examples:

```bash
php -S localhost:8000 -t examples
```

---

## Contributing

Contributions, bug reports, and pull requests are welcome!

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## License

This project is open-sourced software licensed under the **[MIT License](LICENSE)**.

Copyright (c) 2026 **Rui Fernandes**.
