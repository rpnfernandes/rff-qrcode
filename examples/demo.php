<?php declare(strict_types=1);

/**
 * RFF QR Code Examples (Versions 1 to 40 & Custom Dimensions)
 * Run via CLI: php examples/demo.php
 * Or serve via local PHP server: php -S localhost:8000 -t examples
 */

require_once __DIR__ . '/../src/QrCodeGenerator.php';
require_once __DIR__ . '/../src/QrCode.php';

use RFF\QrCode\QrCode;

// 1. Basic SVG generation (default square 200x200)
$svg = QrCode::svg('https://github.com/rfernandes/rff-qrcode');

// 2. Custom dimensions: separate width and height (360x220)
$dimensionSvg = QrCode::svg(
    text: 'https://example.com/custom-dimensions',
    width: 360,
    height: 220,
    fg: '#1e3a8a',
    bg: '#f0f9ff',
    margin: 3
);

// 3. High-capacity payload automatically scaling up to Version 40 (177x177 grid)
$largePayload = str_repeat("Pure PHP QR Code Generator supporting all 40 versions! ", 20); // ~1100 bytes
$v40Svg = QrCode::svg($largePayload, width: 280, height: 280, ecl: QrCode::ECL_M);
$v40Matrix = QrCode::matrix($largePayload, QrCode::ECL_M);
$calculatedVersion = (count($v40Matrix) - 17) / 4;

// 4. Base64 Data URI with custom width & height for inline HTML <img>
$dataUri = QrCode::dataUri('https://example.com/data-uri', width: 260, height: 200);

// 5. Ready-to-use HTML <img> tag with custom dimensions
$imgTag = QrCode::img('https://example.com/image-tag', width: 240, height: 240, alt: 'Scan QR Code');

// If running in CLI, print informative output
if (PHP_SAPI === 'cli') {
    echo "====================================================\n";
    echo "   RFF QR Code - Pure PHP Generator (Versions 1-40) \n";
    echo "====================================================\n\n";

    echo "1. Standard 200x200 SVG length: " . strlen($svg) . " bytes\n";
    echo "2. Custom 360x220 SVG length: " . strlen($dimensionSvg) . " bytes\n";
    echo "3. Large Payload Version: Version " . $calculatedVersion . " (" . count($v40Matrix) . "x" . count($v40Matrix) . " modules)\n";
    echo "4. Data URI prefix: " . substr($dataUri, 0, 45) . "...\n";
    echo "5. HTML <img> Tag:\n   " . substr($imgTag, 0, 75) . "...\" />\n\n";

    // CLI ASCII QR Code Preview (Version 1)
    $previewMatrix = QrCode::matrix('ASCII QR');
    echo "ASCII Preview (Version 1):\n";
    foreach ($previewMatrix as $row) {
        foreach ($row as $col) {
            echo $col ? '██' : '  ';
        }
        echo "\n";
    }

    echo "\nDemo completed successfully!\n";
    exit(0);
}

// If running in browser:
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>RFF QR Code Demo (Versions 1 to 40)</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; padding: 40px; background: #0f172a; color: #f8fafc; }
        .card { background: #1e293b; border-radius: 12px; padding: 24px; margin-bottom: 24px; max-width: 650px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.3); }
        h1 { margin-top: 0; font-size: 26px; color: #38bdf8; }
        h2 { font-size: 18px; color: #94a3b8; margin-top: 0; }
        p { color: #cbd5e1; font-size: 14px; }
        .grid { display: flex; gap: 24px; flex-wrap: wrap; }
    </style>
</head>
<body>
    <h1>RFF QR Code - Pure PHP Library Demo</h1>
    <div class="grid">
        <div class="card">
            <h2>Standard Square (200x200)</h2>
            <?= $svg ?>
        </div>
        <div class="card">
            <h2>Custom Dimensions (360px × 220px)</h2>
            <p>Pass custom width and height parameters independently:</p>
            <?= $dimensionSvg ?>
        </div>
        <div class="card">
            <h2>High Version Scaling (Version <?= $calculatedVersion ?>: <?= count($v40Matrix) ?>×<?= count($v40Matrix) ?> modules)</h2>
            <p>Automatically scales from Version 1 up to Version 40 (max 2,953 bytes):</p>
            <?= $v40Svg ?>
        </div>
        <div class="card">
            <h2>HTML &lt;img&gt; with Data URI</h2>
            <?= $imgTag ?>
        </div>
    </div>
</body>
</html>
