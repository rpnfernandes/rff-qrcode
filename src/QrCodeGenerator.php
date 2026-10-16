<?php declare(strict_types=1);

namespace RFF\QrCode;

/**
 * RFF QR Code Generator Library
 * 
 * Fully compliant ISO/IEC 18004 Standard Pure PHP QR Code Generator.
 * Supports Versions 1 to 40 with ECL (L, M, Q, H), Galois Field GF(256) Reed-Solomon Error Correction,
 * standard block interleaving, 8-mask evaluation with penalty scoring, and standalone vector SVG rendering.
 * 
 * 100% self-contained pure PHP with ZERO external dependencies or Composer packages.
 * 
 * @package     RFF\QrCode
 * @author      Rui Fernandes
 * @license     MIT
 * @version     2.3.0
 */
class QrCodeGenerator
{
    // Error Correction Levels (Format Bits: M=0, L=1, H=2, Q=3)
    public const ECL_L = 1; // ~7% recovery
    public const ECL_M = 0; // ~15% recovery (Default)
    public const ECL_Q = 3; // ~25% recovery
    public const ECL_H = 2; // ~30% recovery

    /**
     * ISO/IEC 18004 Table 9 - Error correction characteristics (Versions 1-40)
     * Format: [TotalCodewords, EcCodewordsPerBlock, G1_Blocks, G1_DataPerBlock, G2_Blocks, G2_DataPerBlock]
     */
private static array $ECC_TABLE = [
        1 => [
            self::ECL_L => [26, 7, 1, 19, 0, 0],
            self::ECL_M => [26, 10, 1, 16, 0, 0],
            self::ECL_Q => [26, 13, 1, 13, 0, 0],
            self::ECL_H => [26, 17, 1, 9, 0, 0],
        ],
        2 => [
            self::ECL_L => [44, 10, 1, 34, 0, 0],
            self::ECL_M => [44, 16, 1, 28, 0, 0],
            self::ECL_Q => [44, 22, 1, 22, 0, 0],
            self::ECL_H => [44, 28, 1, 16, 0, 0],
        ],
        3 => [
            self::ECL_L => [70, 15, 1, 55, 0, 0],
            self::ECL_M => [70, 26, 1, 44, 0, 0],
            self::ECL_Q => [70, 18, 2, 17, 0, 0],
            self::ECL_H => [70, 22, 2, 13, 0, 0],
        ],
        4 => [
            self::ECL_L => [100, 20, 1, 80, 0, 0],
            self::ECL_M => [100, 18, 2, 32, 0, 0],
            self::ECL_Q => [100, 26, 2, 24, 0, 0],
            self::ECL_H => [100, 16, 4, 9, 0, 0],
        ],
        5 => [
            self::ECL_L => [134, 26, 1, 108, 0, 0],
            self::ECL_M => [134, 24, 2, 43, 0, 0],
            self::ECL_Q => [134, 18, 2, 15, 2, 16],
            self::ECL_H => [134, 22, 2, 11, 2, 12],
        ],
        6 => [
            self::ECL_L => [172, 18, 2, 68, 0, 0],
            self::ECL_M => [172, 16, 4, 27, 0, 0],
            self::ECL_Q => [172, 24, 4, 19, 0, 0],
            self::ECL_H => [172, 28, 4, 15, 0, 0],
        ],
        7 => [
            self::ECL_L => [196, 20, 2, 78, 0, 0],
            self::ECL_M => [196, 18, 4, 31, 0, 0],
            self::ECL_Q => [196, 18, 2, 14, 4, 15],
            self::ECL_H => [196, 26, 4, 13, 1, 14],
        ],
        8 => [
            self::ECL_L => [242, 24, 2, 97, 0, 0],
            self::ECL_M => [242, 22, 2, 38, 2, 39],
            self::ECL_Q => [242, 22, 4, 18, 2, 19],
            self::ECL_H => [242, 26, 4, 14, 2, 15],
        ],
        9 => [
            self::ECL_L => [292, 30, 2, 116, 0, 0],
            self::ECL_M => [292, 22, 3, 36, 2, 37],
            self::ECL_Q => [292, 20, 4, 16, 4, 17],
            self::ECL_H => [292, 24, 4, 12, 4, 13],
        ],
        10 => [
            self::ECL_L => [346, 18, 2, 68, 2, 69],
            self::ECL_M => [346, 26, 4, 43, 1, 44],
            self::ECL_Q => [346, 24, 6, 19, 2, 20],
            self::ECL_H => [346, 28, 6, 15, 2, 16],
        ],
        11 => [
            self::ECL_L => [404, 20, 4, 81, 0, 0],
            self::ECL_M => [404, 30, 1, 50, 4, 51],
            self::ECL_Q => [404, 28, 4, 22, 4, 23],
            self::ECL_H => [404, 24, 3, 12, 8, 13],
        ],
        12 => [
            self::ECL_L => [466, 24, 2, 92, 2, 93],
            self::ECL_M => [466, 22, 6, 36, 2, 37],
            self::ECL_Q => [466, 26, 4, 20, 6, 21],
            self::ECL_H => [466, 28, 7, 14, 4, 15],
        ],
        13 => [
            self::ECL_L => [532, 26, 4, 107, 0, 0],
            self::ECL_M => [532, 22, 8, 37, 1, 38],
            self::ECL_Q => [532, 24, 8, 20, 4, 21],
            self::ECL_H => [532, 22, 12, 11, 4, 12],
        ],
        14 => [
            self::ECL_L => [581, 30, 3, 115, 1, 116],
            self::ECL_M => [581, 24, 4, 40, 5, 41],
            self::ECL_Q => [581, 20, 11, 16, 5, 17],
            self::ECL_H => [581, 24, 11, 12, 5, 13],
        ],
        15 => [
            self::ECL_L => [655, 22, 5, 87, 1, 88],
            self::ECL_M => [655, 24, 5, 41, 5, 42],
            self::ECL_Q => [655, 30, 5, 24, 7, 25],
            self::ECL_H => [655, 24, 11, 12, 7, 13],
        ],
        16 => [
            self::ECL_L => [733, 24, 5, 98, 1, 99],
            self::ECL_M => [733, 28, 7, 45, 3, 46],
            self::ECL_Q => [733, 24, 15, 19, 2, 20],
            self::ECL_H => [733, 30, 3, 15, 13, 16],
        ],
        17 => [
            self::ECL_L => [815, 28, 1, 107, 5, 108],
            self::ECL_M => [815, 28, 10, 46, 1, 47],
            self::ECL_Q => [815, 28, 1, 22, 15, 23],
            self::ECL_H => [815, 28, 2, 14, 17, 15],
        ],
        18 => [
            self::ECL_L => [901, 30, 5, 120, 1, 121],
            self::ECL_M => [901, 26, 9, 43, 4, 44],
            self::ECL_Q => [901, 28, 17, 22, 1, 23],
            self::ECL_H => [901, 28, 2, 14, 19, 15],
        ],
        19 => [
            self::ECL_L => [991, 28, 3, 113, 4, 114],
            self::ECL_M => [991, 26, 3, 44, 11, 45],
            self::ECL_Q => [991, 26, 17, 21, 4, 22],
            self::ECL_H => [991, 26, 9, 13, 16, 14],
        ],
        20 => [
            self::ECL_L => [1085, 28, 3, 107, 5, 108],
            self::ECL_M => [1085, 26, 3, 41, 13, 42],
            self::ECL_Q => [1085, 30, 15, 24, 5, 25],
            self::ECL_H => [1085, 28, 15, 15, 10, 16],
        ],
        21 => [
            self::ECL_L => [1156, 28, 4, 116, 4, 117],
            self::ECL_M => [1156, 26, 17, 42, 0, 0],
            self::ECL_Q => [1156, 28, 17, 22, 6, 23],
            self::ECL_H => [1156, 30, 19, 16, 6, 17],
        ],
        22 => [
            self::ECL_L => [1258, 28, 2, 111, 7, 112],
            self::ECL_M => [1258, 28, 17, 46, 0, 0],
            self::ECL_Q => [1258, 30, 7, 24, 16, 25],
            self::ECL_H => [1258, 24, 34, 13, 0, 0],
        ],
        23 => [
            self::ECL_L => [1364, 30, 4, 121, 5, 122],
            self::ECL_M => [1364, 28, 4, 47, 14, 48],
            self::ECL_Q => [1364, 30, 11, 24, 14, 25],
            self::ECL_H => [1364, 30, 16, 15, 14, 16],
        ],
        24 => [
            self::ECL_L => [1474, 30, 6, 117, 4, 118],
            self::ECL_M => [1474, 28, 6, 45, 14, 46],
            self::ECL_Q => [1474, 30, 11, 24, 16, 25],
            self::ECL_H => [1474, 30, 30, 16, 2, 17],
        ],
        25 => [
            self::ECL_L => [1588, 26, 8, 106, 4, 107],
            self::ECL_M => [1588, 28, 8, 47, 13, 48],
            self::ECL_Q => [1588, 30, 7, 24, 22, 25],
            self::ECL_H => [1588, 30, 22, 15, 13, 16],
        ],
        26 => [
            self::ECL_L => [1706, 28, 10, 114, 2, 115],
            self::ECL_M => [1706, 28, 19, 46, 4, 47],
            self::ECL_Q => [1706, 28, 28, 22, 6, 23],
            self::ECL_H => [1706, 30, 33, 16, 4, 17],
        ],
        27 => [
            self::ECL_L => [1828, 30, 8, 122, 4, 123],
            self::ECL_M => [1828, 28, 22, 45, 3, 46],
            self::ECL_Q => [1828, 30, 8, 23, 26, 24],
            self::ECL_H => [1828, 30, 12, 15, 28, 16],
        ],
        28 => [
            self::ECL_L => [1921, 30, 3, 117, 10, 118],
            self::ECL_M => [1921, 28, 3, 45, 23, 46],
            self::ECL_Q => [1921, 30, 4, 24, 31, 25],
            self::ECL_H => [1921, 30, 11, 15, 31, 16],
        ],
        29 => [
            self::ECL_L => [2051, 30, 7, 116, 7, 117],
            self::ECL_M => [2051, 28, 21, 45, 7, 46],
            self::ECL_Q => [2051, 30, 1, 23, 37, 24],
            self::ECL_H => [2051, 30, 19, 15, 26, 16],
        ],
        30 => [
            self::ECL_L => [2185, 30, 5, 115, 10, 116],
            self::ECL_M => [2185, 28, 19, 47, 10, 48],
            self::ECL_Q => [2185, 30, 15, 24, 25, 25],
            self::ECL_H => [2185, 30, 23, 15, 25, 16],
        ],
        31 => [
            self::ECL_L => [2323, 30, 13, 115, 3, 116],
            self::ECL_M => [2323, 28, 2, 46, 29, 47],
            self::ECL_Q => [2323, 30, 42, 24, 1, 25],
            self::ECL_H => [2323, 30, 23, 15, 28, 16],
        ],
        32 => [
            self::ECL_L => [2465, 30, 17, 115, 0, 0],
            self::ECL_M => [2465, 28, 10, 46, 23, 47],
            self::ECL_Q => [2465, 30, 10, 24, 35, 25],
            self::ECL_H => [2465, 30, 19, 15, 35, 16],
        ],
        33 => [
            self::ECL_L => [2611, 30, 17, 115, 1, 116],
            self::ECL_M => [2611, 28, 14, 46, 21, 47],
            self::ECL_Q => [2611, 30, 29, 24, 19, 25],
            self::ECL_H => [2611, 30, 11, 15, 46, 16],
        ],
        34 => [
            self::ECL_L => [2761, 30, 13, 115, 6, 116],
            self::ECL_M => [2761, 28, 14, 46, 23, 47],
            self::ECL_Q => [2761, 30, 44, 24, 7, 25],
            self::ECL_H => [2761, 30, 59, 16, 1, 17],
        ],
        35 => [
            self::ECL_L => [2876, 30, 12, 121, 7, 122],
            self::ECL_M => [2876, 28, 12, 47, 26, 48],
            self::ECL_Q => [2876, 30, 39, 24, 14, 25],
            self::ECL_H => [2876, 30, 22, 15, 41, 16],
        ],
        36 => [
            self::ECL_L => [3034, 30, 6, 121, 14, 122],
            self::ECL_M => [3034, 28, 6, 47, 34, 48],
            self::ECL_Q => [3034, 30, 46, 24, 10, 25],
            self::ECL_H => [3034, 30, 2, 15, 64, 16],
        ],
        37 => [
            self::ECL_L => [3196, 30, 17, 122, 4, 123],
            self::ECL_M => [3196, 28, 29, 46, 14, 47],
            self::ECL_Q => [3196, 30, 49, 24, 10, 25],
            self::ECL_H => [3196, 30, 24, 15, 46, 16],
        ],
        38 => [
            self::ECL_L => [3362, 30, 4, 122, 18, 123],
            self::ECL_M => [3362, 28, 13, 46, 32, 47],
            self::ECL_Q => [3362, 30, 48, 24, 14, 25],
            self::ECL_H => [3362, 30, 42, 15, 32, 16],
        ],
        39 => [
            self::ECL_L => [3532, 30, 20, 117, 4, 118],
            self::ECL_M => [3532, 28, 40, 47, 7, 48],
            self::ECL_Q => [3532, 30, 43, 24, 22, 25],
            self::ECL_H => [3532, 30, 10, 15, 67, 16],
        ],
        40 => [
            self::ECL_L => [3706, 30, 19, 118, 6, 119],
            self::ECL_M => [3706, 28, 18, 47, 31, 48],
            self::ECL_Q => [3706, 30, 34, 24, 34, 25],
            self::ECL_H => [3706, 30, 20, 15, 61, 16],
        ],
    ];

    /**
     * ISO/IEC 18004 Alignment pattern centers (Table E.1, Versions 1-40)
     */
private static array $ALIGNMENT_POSITIONS = [
        1  => [],
        2  => [6, 18],
        3  => [6, 22],
        4  => [6, 26],
        5  => [6, 30],
        6  => [6, 34],
        7  => [6, 22, 38],
        8  => [6, 24, 42],
        9  => [6, 26, 46],
        10 => [6, 28, 50],
        11 => [6, 30, 54],
        12 => [6, 32, 58],
        13 => [6, 34, 62],
        14 => [6, 26, 46, 66],
        15 => [6, 26, 48, 70],
        16 => [6, 26, 50, 74],
        17 => [6, 30, 54, 78],
        18 => [6, 30, 56, 82],
        19 => [6, 30, 58, 86],
        20 => [6, 34, 62, 90],
        21 => [6, 28, 50, 72, 94],
        22 => [6, 26, 50, 74, 98],
        23 => [6, 30, 54, 78, 102],
        24 => [6, 28, 54, 80, 106],
        25 => [6, 32, 58, 84, 110],
        26 => [6, 30, 58, 86, 114],
        27 => [6, 34, 62, 90, 118],
        28 => [6, 26, 50, 74, 98, 122],
        29 => [6, 30, 54, 78, 102, 126],
        30 => [6, 26, 52, 78, 104, 130],
        31 => [6, 30, 56, 82, 108, 134],
        32 => [6, 34, 60, 86, 112, 138],
        33 => [6, 30, 58, 86, 114, 142],
        34 => [6, 34, 62, 90, 118, 146],
        35 => [6, 30, 54, 78, 102, 126, 150],
        36 => [6, 24, 50, 76, 102, 128, 154],
        37 => [6, 28, 54, 80, 106, 132, 158],
        38 => [6, 32, 58, 84, 110, 136, 162],
        39 => [6, 26, 54, 82, 110, 138, 166],
        40 => [6, 30, 58, 86, 114, 142, 170],
    ];

    /**
     * Version information 18-bit BCH codes for Versions 7-40 (Table D.1)
     */
private static array $VERSION_INFO = [
        7  => 0x07C94,
        8  => 0x085BC,
        9  => 0x09A99,
        10 => 0x0A4D3,
        11 => 0x0BBF6,
        12 => 0x0C762,
        13 => 0x0D847,
        14 => 0x0E60D,
        15 => 0x0F928,
        16 => 0x10B78,
        17 => 0x1145D,
        18 => 0x12A17,
        19 => 0x13532,
        20 => 0x149A6,
        21 => 0x15683,
        22 => 0x168C9,
        23 => 0x177EC,
        24 => 0x18EC4,
        25 => 0x191E1,
        26 => 0x1AFAB,
        27 => 0x1B08E,
        28 => 0x1CC1A,
        29 => 0x1D33F,
        30 => 0x1ED75,
        31 => 0x1F250,
        32 => 0x209D5,
        33 => 0x216F0,
        34 => 0x228BA,
        35 => 0x2379F,
        36 => 0x24B0B,
        37 => 0x2542E,
        38 => 0x26A64,
        39 => 0x27541,
        40 => 0x28C69,
    ];

    private static ?array $EXP_TABLE = null;
    private static ?array $LOG_TABLE = null;

    private static function initGaloisField(): void
    {
        if (self::$EXP_TABLE !== null) return;
        self::$EXP_TABLE = array_fill(0, 512, 0);
        self::$LOG_TABLE = array_fill(0, 256, 0);
        $x = 1;
        for ($i = 0; $i < 255; $i++) {
            self::$EXP_TABLE[$i] = $x;
            self::$EXP_TABLE[$i + 255] = $x;
            self::$LOG_TABLE[$x] = $i;
            $x <<= 1;
            if ($x & 0x100) {
                $x ^= 0x11D; // Primitive polynomial x^8 + x^4 + x^3 + x^2 + 1
            }
        }
    }

    private static function gfMul(int $a, int $b): int
    {
        if ($a === 0 || $b === 0) return 0;
        return self::$EXP_TABLE[self::$LOG_TABLE[$a] + self::$LOG_TABLE[$b]];
    }

    /**
     * Generator polynomial: g(x) = prod_{i=0}^{degree-1} (x - alpha^i)
     */
    private static function getGeneratorPoly(int $degree): array
    {
        $poly = [1];
        for ($i = 0; $i < $degree; $i++) {
            $root = self::$EXP_TABLE[$i];
            $next = array_fill(0, count($poly) + 1, 0);
            for ($j = 0; $j < count($poly); $j++) {
                $next[$j] ^= $poly[$j];
                $next[$j + 1] ^= self::gfMul($poly[$j], $root);
            }
            $poly = $next;
        }
        return $poly;
    }

    /**
     * Computes Reed-Solomon error correction codewords
     */
    private static function computeRS(array $data, int $rsCount): array
    {
        self::initGaloisField();
        $gen = self::getGeneratorPoly($rsCount);
        $remainder = array_fill(0, $rsCount, 0);

        foreach ($data as $byte) {
            $factor = $byte ^ $remainder[0];
            array_shift($remainder);
            $remainder[] = 0;
            if ($factor !== 0) {
                for ($j = 0; $j < $rsCount; $j++) {
                    $remainder[$j] ^= self::gfMul($gen[$j + 1], $factor);
                }
            }
        }
        return $remainder;
    }

    /**
     * Generates a 2D boolean array representing dark (true) and light (false) modules.
     * Supports Versions 1 through 40.
     *
     * @param string $text Data to encode
     * @param int $ecl Error Correction Level (ECL_L, ECL_M, ECL_Q, ECL_H)
     * @return array<int, array<int, bool>>
     * @throws \LengthException If data exceeds the capacity of Version 40
     */
    public static function generate(string $text, int $ecl = self::ECL_M): array
    {
        self::initGaloisField();

        $dataBytes = array_values(unpack('C*', $text));
        $dataLen = count($dataBytes);

        // 1. Determine minimum Version needed (Versions 1 to 40)
        $version = 0;
        for ($v = 1; $v <= 40; $v++) {
            $spec = self::$ECC_TABLE[$v][$ecl];
            $totalDataCapacity = ($spec[2] * $spec[3]) + ($spec[4] * $spec[5]);
            $headerBits = 4 + (($v < 10) ? 8 : 16);
            $requiredBits = $headerBits + ($dataLen * 8);
            if ($totalDataCapacity * 8 >= $requiredBits) {
                $version = $v;
                break;
            }
        }

        if ($version === 0) {
            throw new \LengthException(sprintf(
                'Data length (%d bytes) exceeds the maximum capacity of QR Code Version 40 with Error Correction Level %d.',
                $dataLen,
                $ecl
            ));
        }

        $spec = self::$ECC_TABLE[$version][$ecl];
        $totalDataCapacity = ($spec[2] * $spec[3]) + ($spec[4] * $spec[5]);
        $ecCodewordsPerBlock = $spec[1];
        $g1Blocks = $spec[2];
        $g1DataLen = $spec[3];
        $g2Blocks = $spec[4];
        $g2DataLen = $spec[5];

        // 2. Build Bit Stream (Byte Mode: 0100)
        $bits = [];
        self::appendBits($bits, 0b0100, 4);
        $charCountBits = ($version < 10) ? 8 : 16;
        self::appendBits($bits, $dataLen, $charCountBits);

        foreach ($dataBytes as $byte) {
            self::appendBits($bits, $byte, 8);
        }

        // Terminator bits (up to 4 zeroes)
        $totalDataBits = $totalDataCapacity * 8;
        $terminatorLen = min(4, $totalDataBits - count($bits));
        for ($i = 0; $i < $terminatorLen; $i++) {
            $bits[] = 0;
        }

        // Pad to byte boundary
        while (count($bits) % 8 !== 0) {
            $bits[] = 0;
        }

        // Pad bytes 0xEC, 0x11
        $padBytes = [0xEC, 0x11];
        $padIndex = 0;
        while (count($bits) < $totalDataBits) {
            self::appendBits($bits, $padBytes[$padIndex], 8);
            $padIndex = 1 - $padIndex;
        }

        // Convert bits to byte codewords
        $dataCodewords = [];
        for ($i = 0; $i < count($bits); $i += 8) {
            $byte = 0;
            for ($b = 0; $b < 8; $b++) {
                $byte = ($byte << 1) | $bits[$i + $b];
            }
            $dataCodewords[] = $byte;
        }

        // 3. Partition into blocks and calculate Reed-Solomon ECC
        $dataBlocks = [];
        $ecBlocks = [];
        $offset = 0;

        for ($b = 0; $b < $g1Blocks; $b++) {
            $block = array_slice($dataCodewords, $offset, $g1DataLen);
            $dataBlocks[] = $block;
            $ecBlocks[] = self::computeRS($block, $ecCodewordsPerBlock);
            $offset += $g1DataLen;
        }

        for ($b = 0; $b < $g2Blocks; $b++) {
            $block = array_slice($dataCodewords, $offset, $g2DataLen);
            $dataBlocks[] = $block;
            $ecBlocks[] = self::computeRS($block, $ecCodewordsPerBlock);
            $offset += $g2DataLen;
        }

        // 4. Interleave data codewords
        $finalCodewords = [];
        $maxDataLen = max($g1DataLen, $g2DataLen);
        $totalBlocks = count($dataBlocks);

        for ($i = 0; $i < $maxDataLen; $i++) {
            for ($b = 0; $b < $totalBlocks; $b++) {
                if ($i < count($dataBlocks[$b])) {
                    $finalCodewords[] = $dataBlocks[$b][$i];
                }
            }
        }

        // Interleave EC codewords
        for ($i = 0; $i < $ecCodewordsPerBlock; $i++) {
            for ($b = 0; $b < $totalBlocks; $b++) {
                $finalCodewords[] = $ecBlocks[$b][$i];
            }
        }

        // Convert all final codewords into a continuous bitstream
        $finalBits = [];
        foreach ($finalCodewords as $cw) {
            self::appendBits($finalBits, $cw, 8);
        }

        // Add remainder bits according to ISO/IEC 18004 Table 1/7
        $remainderBitsCount = self::getRemainderBitsCount($version);
        for ($i = 0; $i < $remainderBitsCount; $i++) {
            $finalBits[] = 0;
        }

        // 5. Matrix Construction
        $dim = 17 + (4 * $version);
        $matrix = array_fill(0, $dim, array_fill(0, $dim, 0));
        $reserved = array_fill(0, $dim, array_fill(0, $dim, false));

        // Place function patterns
        self::placeFinderPatterns($matrix, $reserved, $dim);
        self::placeTimingPatterns($matrix, $reserved, $dim);
        self::placeAlignmentPatterns($matrix, $reserved, $version);
        self::reserveFormatAreas($reserved, $dim);

        if ($version >= 7) {
            self::reserveVersionAreas($reserved, $dim);
        }

        // Dark module at (4 * version + 9, 8) = (dim - 8, 8)
        $matrix[$dim - 8][8] = 1;
        $reserved[$dim - 8][8] = true;

        // Place data bits into matrix
        self::placeDataBits($matrix, $reserved, $finalBits, $dim);

        // 6. Select Best Mask and Apply
        return self::applyBestMask($matrix, $reserved, $dim, $ecl, $version);
    }

    private static function appendBits(array &$stream, int $val, int $len): void
    {
        for ($i = $len - 1; $i >= 0; $i--) {
            $stream[] = ($val >> $i) & 1;
        }
    }

    /**
     * Remainder bits per ISO/IEC 18004 Table 1/7 for Versions 1-40.
     */
    private static function getRemainderBitsCount(int $version): int
    {
        return match (true) {
            $version >= 2 && $version <= 6 => 7,
            $version >= 14 && $version <= 20 => 3,
            $version >= 21 && $version <= 27 => 4,
            $version >= 28 && $version <= 34 => 3,
            $version >= 35 && $version <= 40 => 4,
            default => 0,
        };
    }

    private static function placeFinderPatterns(array &$matrix, array &$reserved, int $dim): void
    {
        $corners = [[0, 0], [$dim - 7, 0], [0, $dim - 7]];

        foreach ($corners as [$cx, $cy]) {
            for ($y = -1; $y <= 7; $y++) {
                for ($x = -1; $x <= 7; $x++) {
                    $mx = $cx + $x;
                    $my = $cy + $y;
                    if ($mx >= 0 && $mx < $dim && $my >= 0 && $my < $dim) {
                        $reserved[$my][$mx] = true;
                        if ($x >= 0 && $x <= 6 && $y >= 0 && $y <= 6) {
                            $isDark = ($x === 0 || $x === 6 || $y === 0 || $y === 6 || ($x >= 2 && $x <= 4 && $y >= 2 && $y <= 4));
                            $matrix[$my][$mx] = $isDark ? 1 : 0;
                        } else {
                            $matrix[$my][$mx] = 0; // Separator
                        }
                    }
                }
            }
        }
    }

    private static function placeTimingPatterns(array &$matrix, array &$reserved, int $dim): void
    {
        for ($i = 8; $i < $dim - 8; $i++) {
            if (!$reserved[6][$i]) {
                $matrix[6][$i] = ($i % 2 === 0) ? 1 : 0;
                $reserved[6][$i] = true;
            }
            if (!$reserved[$i][6]) {
                $matrix[$i][6] = ($i % 2 === 0) ? 1 : 0;
                $reserved[$i][6] = true;
            }
        }
    }

    private static function placeAlignmentPatterns(array &$matrix, array &$reserved, int $version): void
    {
        $coords = self::$ALIGNMENT_POSITIONS[$version] ?? [];
        $count = count($coords);
        if ($count === 0) return;

        for ($r = 0; $r < $count; $r++) {
            for ($c = 0; $c < $count; $c++) {
                $cy = $coords[$r];
                $cx = $coords[$c];

                // Skip if overlapping with the 3 finder pattern corners
                if (($cx === 6 && $cy === 6) ||
                    ($cx === 6 && $cy === ($coords[$count - 1])) ||
                    ($cx === ($coords[$count - 1]) && $cy === 6)) {
                    continue;
                }

                for ($y = -2; $y <= 2; $y++) {
                    for ($x = -2; $x <= 2; $x++) {
                        $my = $cy + $y;
                        $mx = $cx + $x;
                        $reserved[$my][$mx] = true;
                        $isDark = (abs($x) === 2 || abs($y) === 2 || ($x === 0 && $y === 0));
                        $matrix[$my][$mx] = $isDark ? 1 : 0;
                    }
                }
            }
        }
    }

    private static function reserveFormatAreas(array &$reserved, int $dim): void
    {
        // Top-left
        for ($i = 0; $i <= 8; $i++) {
            $reserved[8][$i] = true;
            $reserved[$i][8] = true;
        }
        // Top-right
        for ($i = 0; $i < 8; $i++) {
            $reserved[8][$dim - 1 - $i] = true;
        }
        // Bottom-left
        for ($i = 0; $i < 8; $i++) {
            $reserved[$dim - 1 - $i][8] = true;
        }
    }

    private static function reserveVersionAreas(array &$reserved, int $dim): void
    {
        for ($i = 0; $i < 6; $i++) {
            for ($j = 0; $j < 3; $j++) {
                // Bottom-left
                $reserved[$dim - 11 + $j][$i] = true;
                // Top-right
                $reserved[$i][$dim - 11 + $j] = true;
            }
        }
    }

    private static function placeDataBits(array &$matrix, array $reserved, array $bits, int $dim): void
    {
        $bitIdx = 0;
        $bitCount = count($bits);
        $up = true;

        for ($x = $dim - 1; $x > 0; $x -= 2) {
            if ($x === 6) {
                $x--; // Skip vertical timing column
            }

            $rows = $up ? range($dim - 1, 0) : range(0, $dim - 1);

            foreach ($rows as $y) {
                foreach ([$x, $x - 1] as $col) {
                    if (!$reserved[$y][$col]) {
                        $matrix[$y][$col] = ($bitIdx < $bitCount) ? $bits[$bitIdx++] : 0;
                    }
                }
            }
            $up = !$up;
        }
    }

    private static function applyBestMask(array $matrix, array $reserved, int $dim, int $ecl, int $version): array
    {
        $bestScore = PHP_INT_MAX;
        $bestMasked = null;

        for ($mask = 0; $mask < 8; $mask++) {
            $candidate = $matrix;

            for ($y = 0; $y < $dim; $y++) {
                for ($x = 0; $x < $dim; $x++) {
                    if (!$reserved[$y][$x]) {
                        if (self::isMaskCondition($mask, $x, $y)) {
                            $candidate[$y][$x] ^= 1;
                        }
                    }
                }
            }

            // Write format info bits
            self::writeFormatInfo($candidate, $ecl, $mask, $dim);

            // Write version info bits for Version >= 7
            if ($version >= 7) {
                self::writeVersionInfo($candidate, $version, $dim);
            }

            $score = self::calculatePenaltyScore($candidate, $dim);
            if ($score < $bestScore) {
                $bestScore = $score;
                $bestMasked = $candidate;
            }
        }

        // Convert to boolean 2D array
        $result = [];
        for ($y = 0; $y < $dim; $y++) {
            $result[$y] = [];
            for ($x = 0; $x < $dim; $x++) {
                $result[$y][$x] = ($bestMasked[$y][$x] === 1);
            }
        }
        return $result;
    }

    private static function isMaskCondition(int $mask, int $x, int $y): bool
    {
        return match ($mask) {
            0 => (($y + $x) % 2 === 0),
            1 => ($y % 2 === 0),
            2 => ($x % 3 === 0),
            3 => (($y + $x) % 3 === 0),
            4 => (((int)floor($y / 2) + (int)floor($x / 3)) % 2 === 0),
            5 => ((($y * $x) % 2) + (($y * $x) % 3) === 0),
            6 => (((($y * $x) % 2) + (($y * $x) % 3)) % 2 === 0),
            7 => (((($y + $x) % 2) + (($y * $x) % 3)) % 2 === 0),
            default => false
        };
    }

    /**
     * ISO/IEC 18004 Format Information placement
     */
    private static function writeFormatInfo(array &$matrix, int $ecl, int $mask, int $dim): void
    {
        $data = ($ecl << 3) | $mask;
        $rem = $data << 10;
        $poly = 0x537;

        for ($i = 4; $i >= 0; $i--) {
            if (($rem >> ($i + 10)) & 1) {
                $rem ^= ($poly << $i);
            }
        }

        $format = (($data << 10) | $rem) ^ 0x5412;

        // Coordinates [x (column), y (row)] around top-left finder (JISX0510:2004 / ISO/IEC 18004)
        $coords = [
            [8, 0], [8, 1], [8, 2], [8, 3], [8, 4], [8, 5],
            [8, 7], [8, 8], [7, 8], [5, 8], [4, 8], [3, 8], [2, 8], [1, 8], [0, 8]
        ];

        for ($i = 0; $i < 15; $i++) {
            $bit = ($format >> $i) & 1;

            // Copy 1 (Around top-left finder): y is row, x is col
            $x1 = $coords[$i][0];
            $y1 = $coords[$i][1];
            $matrix[$y1][$x1] = $bit;

            // Copy 2 (Around top-right and bottom-left finders):
            if ($i < 8) {
                // Top-right corner: col = dim - 1 - i, row = 8
                $x2 = $dim - 1 - $i;
                $y2 = 8;
            } else {
                // Bottom-left corner: col = 8, row = dim - 7 + (i - 8)
                $x2 = 8;
                $y2 = $dim - 7 + ($i - 8);
            }
            $matrix[$y2][$x2] = $bit;
        }
    }

    /**
     * ISO/IEC 18004 Version Information placement (Versions 7-40)
     */
    private static function writeVersionInfo(array &$matrix, int $version, int $dim): void
    {
        $info = self::$VERSION_INFO[$version] ?? null;
        if ($info === null) return;

        for ($i = 0; $i < 18; $i++) {
            $bit = ($info >> $i) & 1;
            $col = (int)floor($i / 3);
            $row = $i % 3;

            // Bottom-left: 3 rows x 6 columns above bottom-left finder
            $matrix[$dim - 11 + $row][$col] = $bit;

            // Top-right: 6 rows x 3 columns left of top-right finder
            $matrix[$col][$dim - 11 + $row] = $bit;
        }
    }

    private static function calculatePenaltyScore(array $matrix, int $dim): int
    {
        $penalty = 0;

        // Rule 1: 5 or more consecutive modules of same color
        for ($y = 0; $y < $dim; $y++) {
            $runColor = -1;
            $runLen = 0;
            for ($x = 0; $x < $dim; $x++) {
                if ($matrix[$y][$x] === $runColor) {
                    $runLen++;
                } else {
                    if ($runLen >= 5) $penalty += 3 + ($runLen - 5);
                    $runColor = $matrix[$y][$x];
                    $runLen = 1;
                }
            }
            if ($runLen >= 5) $penalty += 3 + ($runLen - 5);
        }

        for ($x = 0; $x < $dim; $x++) {
            $runColor = -1;
            $runLen = 0;
            for ($y = 0; $y < $dim; $y++) {
                if ($matrix[$y][$x] === $runColor) {
                    $runLen++;
                } else {
                    if ($runLen >= 5) $penalty += 3 + ($runLen - 5);
                    $runColor = $matrix[$y][$x];
                    $runLen = 1;
                }
            }
            if ($runLen >= 5) $penalty += 3 + ($runLen - 5);
        }

        // Rule 2: 2x2 blocks
        for ($y = 0; $y < $dim - 1; $y++) {
            for ($x = 0; $x < $dim - 1; $x++) {
                $val = $matrix[$y][$x];
                if ($val === $matrix[$y + 1][$x] && $val === $matrix[$y][$x + 1] && $val === $matrix[$y + 1][$x + 1]) {
                    $penalty += 3;
                }
            }
        }

        return $penalty;
    }

    /**
     * Renders the QR code directly as a standalone vector SVG string.
     *
     * @param string $text Data to encode
     * @param int $width Width in pixels (default: 200)
     * @param int|null $height Height in pixels (default: null, matches width)
     * @param string $fg Foreground color CSS hex or named color (default: '#000000')
     * @param string $bg Background color or 'transparent' (default: '#ffffff')
     * @param int $margin Quiet zone margin in modules (default: 4)
     * @param int $ecl Error Correction Level (default: ECL_M)
     * @param int|null $size Optional square size override (for backwards compatibility)
     * @return string Valid XML SVG markup
     */
    public static function renderSvg(
        string $text,
        int $width = 200,
        ?int $height = null,
        string $fg = '#000000',
        string $bg = '#ffffff',
        int $margin = 4,
        int $ecl = self::ECL_M,
        ?int $size = null
    ): string {
        if ($size !== null) {
            $width = $size;
            $height = $size;
        } elseif ($height === null) {
            $height = $width;
        }

        $matrix = self::generate($text, $ecl);
        $dim = count($matrix);
        $totalDim = $dim + ($margin * 2);

        $pathD = '';
        for ($y = 0; $y < $dim; $y++) {
            for ($x = 0; $x < $dim; $x++) {
                if ($matrix[$y][$x]) {
                    $mx = $x + $margin;
                    $my = $y + $margin;
                    $pathD .= "M{$mx},{$my}h1v1h-1z ";
                }
            }
        }

        $bgRect = ($bg !== 'transparent') ? "<rect width=\"{$totalDim}\" height=\"{$totalDim}\" fill=\"{$bg}\"/>" : '';

        return "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 {$totalDim} {$totalDim}\" width=\"{$width}\" height=\"{$height}\" shape-rendering=\"crispEdges\">{$bgRect}<path d=\"{$pathD}\" fill=\"{$fg}\"/></svg>";
    }

    /**
     * Renders the QR code as a base64 Data URI suitable for img src.
     *
     * @param string $text Data to encode
     * @param int $width Width in pixels (default: 200)
     * @param int|null $height Height in pixels (default: null, matches width)
     * @param string $fg Foreground color CSS hex or named color (default: '#000000')
     * @param string $bg Background color or 'transparent' (default: '#ffffff')
     * @param int $margin Quiet zone margin in modules (default: 4)
     * @param int $ecl Error Correction Level (default: ECL_M)
     * @param int|null $size Optional square size override (for backwards compatibility)
     * @return string Data URI string (`data:image/svg+xml;base64,...`)
     */
    public static function renderDataUriSvg(
        string $text,
        int $width = 200,
        ?int $height = null,
        string $fg = '#000000',
        string $bg = '#ffffff',
        int $margin = 4,
        int $ecl = self::ECL_M,
        ?int $size = null
    ): string {
        $svg = self::renderSvg($text, $width, $height, $fg, $bg, $margin, $ecl, $size);
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * Renders an HTML <img> tag with embedded SVG Data URI.
     *
     * @param string $text Data to encode
     * @param int $width Width in pixels (default: 200)
     * @param int|null $height Height in pixels (default: null, matches width)
     * @param string $alt Alternate text attribute (default: 'QR Code')
     * @param string $fg Foreground color CSS hex or named color (default: '#000000')
     * @param string $bg Background color or 'transparent' (default: '#ffffff')
     * @param int $margin Quiet zone margin in modules (default: 4)
     * @param int $ecl Error Correction Level (default: ECL_M)
     * @param int|null $size Optional square size override (for backwards compatibility)
     * @return string Complete HTML <img ... /> tag
     */
    public static function renderHtmlImg(
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
        if ($size !== null) {
            $width = $size;
            $height = $size;
        } elseif ($height === null) {
            $height = $width;
        }

        $dataUri = self::renderDataUriSvg($text, $width, $height, $fg, $bg, $margin, $ecl);
        $escapedAlt = htmlspecialchars($alt, ENT_QUOTES, 'UTF-8');
        return "<img src=\"{$dataUri}\" width=\"{$width}\" height=\"{$height}\" alt=\"{$escapedAlt}\" />";
    }
}