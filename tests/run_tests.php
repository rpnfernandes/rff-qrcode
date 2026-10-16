<?php declare(strict_types=1);

/**
 * Standalone test runner (no composer / phpunit required).
 * Run: php tests/run_tests.php
 */

require_once __DIR__ . '/../src/QrCodeGenerator.php';
require_once __DIR__ . '/../src/QrCode.php';

use RFF\QrCode\QrCode;
use RFF\QrCode\QrCodeGenerator;

$passed = 0;
$failed = 0;

function it(string $description, callable $fn): void {
    global $passed, $failed;
    try {
        $fn();
        echo "  [PASS] {$description}\n";
        $passed++;
    } catch (\Throwable $e) {
        echo "  [FAIL] {$description}: " . $e->getMessage() . "\n";
        $failed++;
    }
}

echo "Running RFF QR Code Standalone Test Suite (Versions 1-40)...\n\n";

it('Generates boolean matrix with correct dimensions (Version 1)', function () {
    $matrix = QrCodeGenerator::generate('Hello World');
    assert(is_array($matrix));
    assert(count($matrix) === 21);
    assert(count($matrix[0]) === 21);
    assert(is_bool($matrix[0][0]));
});

it('Renders valid vector SVG with default and custom size', function () {
    $svg = QrCodeGenerator::renderSvg('https://example.com', 250);
    assert(str_starts_with($svg, '<svg xmlns="http://www.w3.org/2000/svg"'));
    assert(str_ends_with($svg, '</svg>'));
    assert(str_contains($svg, 'width="250"'));
    assert(str_contains($svg, 'height="250"'));
});

it('Renders SVG with separate width and height', function () {
    $svg = QrCodeGenerator::renderSvg('Dimensions Test', width: 420, height: 260);
    assert(str_contains($svg, 'width="420"'));
    assert(str_contains($svg, 'height="260"'));
});

it('Maintains backward compatibility with size parameter', function () {
    $svg = QrCodeGenerator::renderSvg('Size Test', size: 380);
    assert(str_contains($svg, 'width="380"'));
    assert(str_contains($svg, 'height="380"'));
});

it('Supports transparent background', function () {
    $svg = QrCodeGenerator::renderSvg('Transparent', 200, null, '#00ff00', 'transparent');
    assert(!str_contains($svg, '<rect'));
    assert(str_contains($svg, 'fill="#00ff00"'));
});

it('Renders valid Data URI with width and height', function () {
    $uri = QrCodeGenerator::renderDataUriSvg('Data URI', 300, 200);
    assert(str_starts_with($uri, 'data:image/svg+xml;base64,'));
    $decoded = base64_decode(substr($uri, strlen('data:image/svg+xml;base64,')), true);
    assert($decoded !== false);
    assert(str_starts_with($decoded, '<svg'));
    assert(str_contains($decoded, 'width="300"'));
    assert(str_contains($decoded, 'height="200"'));
});

it('Renders HTML <img> tag with width and height', function () {
    $html = QrCodeGenerator::renderHtmlImg('HTML Image', 320, 240, 'My QR Code');
    assert(str_starts_with($html, '<img src="data:image/svg+xml;base64,'));
    assert(str_contains($html, 'width="320"'));
    assert(str_contains($html, 'height="240"'));
    assert(str_contains($html, 'alt="My QR Code"'));
});

it('Supports all 4 Error Correction Levels', function () {
    foreach ([QrCodeGenerator::ECL_L, QrCodeGenerator::ECL_M, QrCodeGenerator::ECL_Q, QrCodeGenerator::ECL_H] as $ecl) {
        $svg = QrCodeGenerator::renderSvg('ECL Test', 200, null, '#000', '#fff', 4, $ecl);
        assert(str_starts_with($svg, '<svg'));
    }
});

it('Supports QrCode shorthand facade with width and height', function () {
    $svg = QrCode::svg('Shorthand Test', width: 500, height: 300);
    $uri = QrCode::dataUri('Shorthand Test', width: 500, height: 300);
    $img = QrCode::img('Shorthand Test', width: 500, height: 300);
    $mat = QrCode::matrix('Shorthand Test');

    assert(str_contains($svg, 'width="500"'));
    assert(str_contains($svg, 'height="300"'));
    assert(str_starts_with($uri, 'data:image/svg+xml;base64,'));
    assert(str_contains($img, 'width="500"'));
    assert(str_contains($img, 'height="300"'));
    assert(is_array($mat));
});

it('Scales up to Version 40 (177x177 modules)', function () {
    $payload = str_repeat('V40_DATA_', 320); // ~2880 bytes
    $matrix = QrCode::matrix($payload, QrCode::ECL_L);
    $dim = count($matrix);
    $version = ($dim - 17) / 4;
    assert($version === 40);
    assert($dim === 177);
    assert(count($matrix[0]) === 177);
});

it('Throws LengthException when payload exceeds Version 40 capacity', function () {
    $caught = false;
    try {
        $huge = str_repeat('TOO_LARGE_', 500); // 5000 bytes
        QrCode::generate($huge);
    } catch (\LengthException $e) {
        $caught = true;
    }
    assert($caught === true);
});

it('Backward compatibility shim works', function () {
    require_once __DIR__ . '/../QrCodeGenerator.php';
    assert(class_exists('QrCodeGenerator'));
    $svg = \QrCodeGenerator::renderSvg('Legacy Class Test', 250);
    assert(str_starts_with($svg, '<svg'));
    assert(str_contains($svg, 'width="250"'));
});

echo "\nResult: {$passed} passed, {$failed} failed.\n";
exit($failed > 0 ? 1 : 0);
