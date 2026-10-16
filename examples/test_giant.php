<?php declare(strict_types=1);

/**
 * Teste de QR Code com String Gigante (sem necessidade de servidor web)
 * 
 * Executar via Terminal:
 *   php examples/test_giant.php
 */

require_once __DIR__ . '/../src/QrCodeGenerator.php';
require_once __DIR__ . '/../src/QrCode.php';

use RFF\QrCode\QrCode;

// Podes passar um texto customizado pela linha de comandos:
// php examples/test_giant.php "O teu texto aqui..."
// Ou deixar vazio para gerar automaticamente uma string gigante de ~2.500 caracteres
if (!empty($argv[1])) {
    $text = $argv[1];
} else {
    $base = "RFF-QRCode: Pure PHP QR Code Generator ISO/IEC 18004. ";
    $text = "";
    while (strlen($text) < 2500) {
        $text .= $base . "Texto de teste para validar a matriz de alta densidade (Versões 1 a 40). ";
    }
    // Ajustar para ~2.500 caracteres (dentro do limite máximo da Versão 40)
    $text = substr($text, 0, 2500);
}

$bytes = strlen($text);
echo "========================================================\n";
echo "    RFF QR Code - Teste com String Gigante (PHP Puro)  \n";
echo "========================================================\n\n";
echo "A codificar string com $bytes bytes...\n";

$start = microtime(true);

// Gerar o SVG com tamanho 600x600 e nível de correção de erro L para máxima capacidade
$svg = QrCode::svg($text, width: 600, height: 600, ecl: QrCode::ECL_L);

$duration = round((microtime(true) - $start) * 1000, 2);

// Obter dados da matriz
$matrix = QrCode::matrix($text, QrCode::ECL_L);
$dim = count($matrix);
$version = ($dim - 17) / 4;

echo "-> Versão QR Code alcançada: Versão {$version}\n";
echo "-> Resolução da matriz: {$dim}x{$dim} módulos (" . ($dim * $dim) . " células)\n";
echo "-> Tempo total de processamento: {$duration} ms\n";
echo "-> Tamanho do SVG vetorial gerado: " . number_format(strlen($svg)) . " bytes\n\n";

// Criar página HTML estilizada para pré-visualização imediata
$htmlFile = dirname(__DIR__) . '/giant_qr_test.html';
$svgFile = dirname(__DIR__) . '/giant_qr.svg';

file_put_contents($svgFile, $svg);

$escapedText = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
$html = <<<HTML
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Teste QR Code Gigante - Versão {$version}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: #0f172a;
            color: #f8fafc;
            margin: 0;
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .container {
            max-width: 800px;
            background: #1e293b;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
            text-align: center;
        }
        h1 {
            color: #38bdf8;
            margin-top: 0;
            font-size: 26px;
        }
        .stats {
            display: flex;
            justify-content: space-around;
            background: #0f172a;
            padding: 16px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .stat-item {
            display: flex;
            flex-direction: column;
        }
        .stat-label {
            font-size: 12px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .stat-value {
            font-size: 20px;
            font-weight: bold;
            color: #f1f5f9;
            margin-top: 4px;
        }
        .qr-wrapper {
            background: #ffffff;
            padding: 24px;
            border-radius: 12px;
            display: inline-block;
            margin: 20px 0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        textarea {
            width: 100%;
            height: 120px;
            box-sizing: border-box;
            background: #0b1120;
            color: #38bdf8;
            border: 1px solid #334155;
            border-radius: 8px;
            padding: 12px;
            font-family: monospace;
            font-size: 13px;
            resize: vertical;
        }
        .hint {
            color: #94a3b8;
            font-size: 13px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>QR Code Gigante - Teste de Capacidade</h1>
        <div class="stats">
            <div class="stat-item">
                <span class="stat-label">Versão ISO</span>
                <span class="stat-value">Versão {$version}</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Matriz</span>
                <span class="stat-value">{$dim} × {$dim}</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Carga de Dados</span>
                <span class="stat-value">{$bytes} bytes</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Tempo Geração</span>
                <span class="stat-value">{$duration} ms</span>
            </div>
        </div>

        <div class="qr-wrapper">
            {$svg}
        </div>

        <p class="hint">Podes fazer zoom ou ler com a câmara do telemóvel diretamente do monitor!</p>

        <h3 style="text-align: left; color: #cbd5e1; font-size: 16px; margin-top: 24px;">Conteúdo da String Codificada:</h3>
        <textarea readonly>{$escapedText}</textarea>
    </div>
</body>
</html>
HTML;

file_put_contents($htmlFile, $html);

echo "-> Ficheiro HTML gerado em: $htmlFile\n";
echo "-> Ficheiro SVG gerado em: $svgFile\n\n";

// No macOS, podemos abrir diretamente o ficheiro no navegador sem servidor
if (PHP_OS_FAMILY === 'Darwin') {
    echo "A abrir o ficheiro no teu navegador padrão...\n";
    @exec('open ' . escapeshellarg($htmlFile));
}

echo "Sucesso!\n";
