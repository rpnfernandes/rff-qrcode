<?php declare(strict_types=1);

/**
 * Backward compatibility loader for legacy non-PSR-4 projects.
 * For modern Composer projects, use `RFF\QrCode\QrCodeGenerator` or `RFF\QrCode\QrCode`.
 */

require_once __DIR__ . '/src/QrCodeGenerator.php';
require_once __DIR__ . '/src/QrCode.php';

if (!class_exists('QrCodeGenerator', false)) {
    class_alias(\RFF\QrCode\QrCodeGenerator::class, 'QrCodeGenerator');
}

if (!class_exists('QrCode', false)) {
    class_alias(\RFF\QrCode\QrCode::class, 'QrCode');
}
