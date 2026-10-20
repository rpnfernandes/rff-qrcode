# Changelog

All notable changes to `rpnfernandes/rff-qrcode` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-10-20

### Added
- Complete zero-dependency pure PHP QR Code Generator compliant with ISO/IEC 18004.
- Support for QR Model 2 from **Version 1** ($21 \times 21$) up to **Version 40** ($177 \times 177$).
- Full Reed-Solomon Error Correction across Galois Field $\text{GF}(256)$ for all error correction levels:
  - Low (`ECL_L`, ~7%)
  - Medium (`ECL_M`, ~15%, default)
  - Quartile (`ECL_Q`, ~25%)
  - High (`ECL_H`, ~30%)
- Automatic optimal 8-pattern mask evaluation with standard penalty scoring.
- High-performance vector SVG generation (`QrCode::svg()`, `QrCodeGenerator::renderSvg()`).
- Data URI Base64 SVG generation (`QrCode::dataUri()`, `QrCodeGenerator::renderDataUriSvg()`).
- HTML `<img>` tag generation helper (`QrCode::img()`, `QrCodeGenerator::renderHtmlImg()`).
- Raw boolean matrix generation (`QrCode::matrix()`).
- Support for independent custom width and height rendering.
- Configurable quiet zone margin, foreground color, and background color (including `'transparent'`).
- Ergonomic static facade `RFF\QrCode\QrCode` and backward compatibility shims.
- Comprehensive PHPUnit and standalone zero-dependency test suite running across PHP 8.0, 8.1, 8.2, 8.3, and 8.4.
