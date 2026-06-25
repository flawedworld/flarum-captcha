<?php

namespace GrapheneOS\Captcha;

class ImageGenerator
{
    private const WIDTH   = 220;
    private const HEIGHT  = 80;
    private const LENGTH  = 6;
    private const CHARSET = 'ACDEFGHJKLMNPQRTUVWXY3479';

    // 5×7 bitmap glyphs for each character in CHARSET.
    // Each entry is 7 rows of 5 bits, stored as a 35-char string of '0'/'1'.
    // Row 0 = top, bit 0 = leftmost pixel.
    private const GLYPHS = [
        'A' => '01110' . '10001' . '10001' . '11111' . '10001' . '10001' . '10001',
        'C' => '01110' . '10001' . '10000' . '10000' . '10000' . '10001' . '01110',
        'D' => '11110' . '10001' . '10001' . '10001' . '10001' . '10001' . '11110',
        'E' => '11111' . '10000' . '10000' . '11110' . '10000' . '10000' . '11111',
        'F' => '11111' . '10000' . '10000' . '11110' . '10000' . '10000' . '10000',
        'G' => '01110' . '10001' . '10000' . '10111' . '10001' . '10001' . '01110',
        'H' => '10001' . '10001' . '10001' . '11111' . '10001' . '10001' . '10001',
        'J' => '00111' . '00010' . '00010' . '00010' . '10010' . '10010' . '01100',
        'K' => '10001' . '10010' . '10100' . '11000' . '10100' . '10010' . '10001',
        'L' => '10000' . '10000' . '10000' . '10000' . '10000' . '10000' . '11111',
        'M' => '10001' . '11011' . '10101' . '10101' . '10001' . '10001' . '10001',
        'N' => '10001' . '11001' . '10101' . '10101' . '10011' . '10001' . '10001',
        'P' => '11110' . '10001' . '10001' . '11110' . '10000' . '10000' . '10000',
        'Q' => '01110' . '10001' . '10001' . '10001' . '10101' . '10010' . '01101',
        'R' => '11110' . '10001' . '10001' . '11110' . '10100' . '10010' . '10001',
        'T' => '11111' . '00100' . '00100' . '00100' . '00100' . '00100' . '00100',
        'U' => '10001' . '10001' . '10001' . '10001' . '10001' . '10001' . '01110',
        'V' => '10001' . '10001' . '10001' . '10001' . '01010' . '01010' . '00100',
        'W' => '10001' . '10001' . '10001' . '10101' . '10101' . '11011' . '10001',
        'X' => '10001' . '10001' . '01010' . '00100' . '01010' . '10001' . '10001',
        'Y' => '10001' . '10001' . '01010' . '00100' . '00100' . '00100' . '00100',
        '3' => '11110' . '00001' . '00001' . '01110' . '00001' . '00001' . '11110',
        '4' => '10001' . '10001' . '10001' . '11111' . '00001' . '00001' . '00001',
        '7' => '11111' . '00001' . '00010' . '00100' . '01000' . '01000' . '01000',
        '9' => '01110' . '10001' . '10001' . '01111' . '00001' . '10001' . '01110',
    ];

    public function generate(): array
    {
        $text   = $this->randomText();
        $canvas = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        $bg     = imagecolorallocate($canvas, 245, 245, 245);
        imagefill($canvas, 0, 0, $bg);

        $this->drawNoise($canvas);
        $this->drawText($canvas, $text);
        $this->drawLines($canvas);

        ob_start();
        imagepng($canvas);
        $png = ob_get_clean();
        imagedestroy($canvas);

        return ['text' => $text, 'png' => $png];
    }

    private function randomText(): string
    {
        $out = '';
        $max = strlen(self::CHARSET) - 1;
        for ($i = 0; $i < self::LENGTH; $i++) {
            $out .= self::CHARSET[random_int(0, $max)];
        }
        return $out;
    }

    private function drawText(\GdImage $image, string $text): void
    {
        // Each glyph cell: 5 cols × 7 rows of pixels, drawn at PIXEL_SIZE each.
        // We pick a pixel size that fills ~75% of the image height.
        $pixelSize = 5;   // each "pixel" = 5×5 actual pixels → glyph = 25×35px, 6 chars = 200px
        $glyphW    = 5 * $pixelSize;
        $glyphH    = 7 * $pixelSize;

        $totalW = self::LENGTH * $glyphW + (self::LENGTH - 1) * $pixelSize * 2; // with spacing
        $startX = (int) ((self::WIDTH - $totalW) / 2);
        $centerY = (int) (self::HEIGHT / 2);

        for ($i = 0; $i < self::LENGTH; $i++) {
            $ch    = $text[$i];
            $glyph = self::GLYPHS[$ch] ?? self::GLYPHS['A'];

            $color = imagecolorallocate(
                $image,
                random_int(10, 60),
                random_int(10, 60),
                random_int(10, 60)
            );

            $jitter = random_int(-4, 4);
            $x0 = $startX + $i * ($glyphW + $pixelSize * 2);
            $y0 = $centerY - (int) ($glyphH / 2) + $jitter;

            for ($row = 0; $row < 7; $row++) {
                for ($col = 0; $col < 5; $col++) {
                    if ($glyph[$row * 5 + $col] === '1') {
                        $px = $x0 + $col * $pixelSize;
                        $py = $y0 + $row * $pixelSize;
                        imagefilledrectangle($image, $px, $py, $px + $pixelSize - 1, $py + $pixelSize - 1, $color);
                    }
                }
            }
        }
    }

    private function drawNoise(\GdImage $image): void
    {
        $pixels = (int) (self::WIDTH * self::HEIGHT * 0.06);
        for ($i = 0; $i < $pixels; $i++) {
            $c = imagecolorallocatealpha(
                $image,
                random_int(80, 180),
                random_int(80, 180),
                random_int(80, 180),
                random_int(40, 80)
            );
            imagesetpixel($image, random_int(0, self::WIDTH - 1), random_int(0, self::HEIGHT - 1), $c);
        }
    }

    private function drawLines(\GdImage $image): void
    {
        for ($i = 0; $i < 4; $i++) {
            $c = imagecolorallocatealpha(
                $image,
                random_int(80, 160),
                random_int(80, 160),
                random_int(80, 160),
                random_int(50, 90)
            );
            imageline(
                $image,
                random_int(0, self::WIDTH),  random_int(0, self::HEIGHT),
                random_int(0, self::WIDTH),  random_int(0, self::HEIGHT),
                $c
            );
        }
    }
}
