<?php declare(strict_types=1);

namespace RFF\QrCode\Tests;

use PHPUnit\Framework\TestCase;
use RFF\QrCode\QrCode;
use RFF\QrCode\QrCodeGenerator;

class QrCodeGeneratorTest extends TestCase
{
    public function testGenerateReturnsBooleanMatrix(): void
    {
        $matrix = QrCodeGenerator::generate('Hello World');

        $this->assertIsArray($matrix);
        $this->assertNotEmpty($matrix);
        $dim = count($matrix);

        // Version 1 is 21x21
        $this->assertSame(21, $dim);
        $this->assertCount(21, $matrix[0]);
        $this->assertIsBool($matrix[0][0]);
    }

    public function testRenderSvgOutputStructure(): void
    {
        $svg = QrCodeGenerator::renderSvg('https://github.com/rpnfernandes/rff-qrcode', 300);

        $this->assertStringStartsWith('<svg xmlns="http://www.w3.org/2000/svg"', $svg);
        $this->assertStringEndsWith('</svg>', $svg);
        $this->assertStringContainsString('width="300"', $svg);
        $this->assertStringContainsString('height="300"', $svg);
        $this->assertStringContainsString('<path d="', $svg);
        $this->assertStringContainsString('<rect width="', $svg);
    }

    public function testRenderSvgCustomWidthAndHeight(): void
    {
        $svg = QrCodeGenerator::renderSvg('Testing Dimensions', width: 450, height: 280);

        $this->assertStringContainsString('width="450"', $svg);
        $this->assertStringContainsString('height="280"', $svg);
    }

    public function testRenderSvgSizeParameterBackwardCompatibility(): void
    {
        $svg = QrCodeGenerator::renderSvg('Size Test', size: 350);

        $this->assertStringContainsString('width="350"', $svg);
        $this->assertStringContainsString('height="350"', $svg);
    }

    public function testRenderSvgTransparentBackground(): void
    {
        $svg = QrCodeGenerator::renderSvg('Testing Transparent', 200, null, '#112233', 'transparent');

        $this->assertStringNotContainsString('<rect', $svg);
        $this->assertStringContainsString('fill="#112233"', $svg);
    }

    public function testRenderDataUriSvgFormat(): void
    {
        $dataUri = QrCodeGenerator::renderDataUriSvg('Data URI Test', 300, 200);

        $this->assertStringStartsWith('data:image/svg+xml;base64,', $dataUri);
        $base64 = substr($dataUri, strlen('data:image/svg+xml;base64,'));
        $decoded = base64_decode($base64, true);

        $this->assertNotFalse($decoded);
        $this->assertStringStartsWith('<svg', $decoded);
        $this->assertStringEndsWith('</svg>', $decoded);
        $this->assertStringContainsString('width="300"', $decoded);
        $this->assertStringContainsString('height="200"', $decoded);
    }

    public function testRenderHtmlImgWithCustomWidthAndHeight(): void
    {
        $img = QrCodeGenerator::renderHtmlImg('HTML Image Test', 320, 240, 'Scan Me');

        $this->assertStringStartsWith('<img src="data:image/svg+xml;base64,', $img);
        $this->assertStringContainsString('width="320"', $img);
        $this->assertStringContainsString('height="240"', $img);
        $this->assertStringContainsString('alt="Scan Me"', $img);
    }

    public function testErrorCorrectionLevels(): void
    {
        $levels = [
            QrCodeGenerator::ECL_L,
            QrCodeGenerator::ECL_M,
            QrCodeGenerator::ECL_Q,
            QrCodeGenerator::ECL_H,
        ];

        foreach ($levels as $ecl) {
            $svg = QrCodeGenerator::renderSvg('Error Correction Test', 200, null, '#000000', '#ffffff', 4, $ecl);
            $this->assertNotEmpty($svg);
            $this->assertStringStartsWith('<svg', $svg);
        }
    }

    public function testQrCodeFacadeMethodsWithDimensions(): void
    {
        $svg = QrCode::svg('Facade Test', width: 400, height: 250);
        $dataUri = QrCode::dataUri('Facade Test', width: 400, height: 250);
        $img = QrCode::img('Facade Test', width: 400, height: 250);
        $matrix = QrCode::matrix('Facade Test');

        $this->assertStringContainsString('width="400"', $svg);
        $this->assertStringContainsString('height="250"', $svg);
        $this->assertStringStartsWith('data:image/svg+xml;base64,', $dataUri);
        $this->assertStringContainsString('width="400"', $img);
        $this->assertStringContainsString('height="250"', $img);
        $this->assertIsArray($matrix);
    }

    public function testScalesUpToVersion40(): void
    {
        // Version 40 maximum test (~2950 bytes in ECL_L)
        $payload = str_repeat('V40_DATA_CHUNK_', 195); // ~2925 bytes
        $matrix = QrCode::matrix($payload, QrCode::ECL_L);

        $this->assertIsArray($matrix);
        $dim = count($matrix);
        $version = ($dim - 17) / 4;

        $this->assertSame(40, $version);
        $this->assertSame(177, $dim);
        $this->assertCount(177, $matrix[0]);
    }

    public function testThrowsExceptionWhenDataExceedsVersion40Capacity(): void
    {
        $this->expectException(\LengthException::class);

        // Huge payload that exceeds Version 40
        $hugePayload = str_repeat('EXCEEDS_CAPACITY_', 300); // 5100 bytes
        QrCode::generate($hugePayload, QrCode::ECL_M);
    }
}
