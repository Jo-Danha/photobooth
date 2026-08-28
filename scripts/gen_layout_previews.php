<?php
/**
 * Generate preview PNG statis yang MENARIK untuk halaman "Pilih Format & Layout".
 * Setiap preview menampilkan siluet orang (kepala+bahu) dengan FILTER/animasi
 * sesuai layout (dog=hidung+tel. anjing, hearts=mata hati, dll). Bukan kotak abstrak.
 *
 * Output: public/layout-previews/{layout_id}.png
 * Jalankan: php scripts/gen_layout_previews.php
 */

if (!extension_loaded('gd')) { echo "GD tidak tersedia\n"; exit(1); }

$outDir = __DIR__ . '/../public/layout-previews';
if (!is_dir($outDir)) mkdir($outDir, 0775, true);

// ukuran & grid per layout (w, h, cols, rows, unique_id_brand)
$defs = [
    ['strip_4',   400, 1164, 1, 4, 'Layout B'],
    ['strip_3',   400,  906, 1, 3, 'Layout A'],
    ['strip_2',   400,  648, 1, 2, 'Layout C'],
    ['strip_6',   678,  861, 2, 3, 'Layout D'],
    ['strip_e',   678,  618, 2, 2, 'Layout E'],
    ['grid_4',    678,  618, 2, 2, 'GRID 4'],
    ['polaroid',  420,  500, 1, 1, 'POLAROID'],
// themes (strip 1 col 4 photos)
    ['hearts',    400, 1164, 1, 4, 'HEARTS'],
    ['dog',       400, 1164, 1, 4, 'DOG'],
    ['vintage',   400, 1164, 1, 4, 'VINTAGE'],
    ['solace',    400, 1164, 1, 4, 'SOLACE'],
    ['classic',   400, 1164, 1, 4, 'CLASSIC'],
    ['with_love', 400, 1164, 1, 4, 'WITH LOVE'],
    ['holidays',  400, 1164, 1, 4, 'HOLIDAYS'],
];

$themedIds = ['hearts', 'dog', 'vintage', 'solace', 'classic', 'with_love', 'holidays'];

function col($im, $hex, $a = 0) {
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) $hex = $hex . $hex;
    return imagecolorallocatealpha($im, hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2)), $a);
}
function hex2rgb($hex) {
    $hex = ltrim($hex, '#'); if (strlen($hex) === 3) $hex = $hex . $hex;
    return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
}
function vgrad($im, $x, $y, $w, $h, $ht, $hb) {
    $g = imagecreatetruecolor($w, $h);
    $t = hex2rgb($ht); $b = hex2rgb($hb);
    for ($yy = 0; $yy < $h; $yy++) {
        $f = $h > 1 ? $yy / ($h - 1) : 0;
        $c = imagecolorallocate($g,
            (int)round($t[0] + ($b[0] - $t[0]) * $f),
            (int)round($t[1] + ($b[1] - $t[1]) * $f),
            (int)round($t[2] + ($b[2] - $t[2]) * $f));
        imageline($g, 0, $yy, $w, $yy, $c);
    }
    imagecopy($im, $g, $x, $y, 0, 0, $w, $h);
    imagedestroy($g);
}
function drawHeart($im, $cx, $cy, $s, $c) {
    imagefilledellipse($im, (int)($cx - $s * 0.4), (int)($cy - $s * 0.35), (int)($s), (int)($s), $c);
    imagefilledellipse($im, (int)($cx + $s * 0.4), (int)($cy - $s * 0.35), (int)($s), (int)($s), $c);
    imagefilledpolygon($im, [
        (int)($cx - $s * 0.9), (int)($cy - $s * 0.30),
        (int)($cx + $s * 0.9), (int)($cy - $s * 0.30),
        $cx, (int)($cy + $s * 0.70),
    ], 3, $c);
}
function drawPortrait($im, $x, $y, $w, $h, $theme, $i) {
    $cx = $x + (int)($w / 2);
    $headR = (int)($h * 0.22);
    $headCy = $y + (int)($h * 0.34);
    $skin = ['#c99778', '#e7b08e', '#f3cdb4', '#dbae8b'][$i % 4];
    $outline = '#4a3222';
    $eye = col($im, $outline);
    $skinCol = col($im, $skin);
    // bahu
    imagefilledellipse($im, $cx, $y + $h + $headR, (int)($w * 0.80), (int)($h * 0.55), $skinCol);
    // kepala
    imagefilledellipse($im, $cx, $headCy, $headR * 2, (int)($headR * 2.1), $skinCol);
    // leher
    imagefilledrectangle($im, $cx - (int)($w * 0.08), $headCy + (int)($headR), $cx + (int)($w * 0.08), $headCy + (int)($headR * 1.4), $skinCol);

    $eyeY = $headCy - (int)($headR * 0.12);
    $eyeOff = (int)($headR * 0.5);
    $smileY = $headCy + (int)($headR * 0.42);
    imageline($im, $cx - (int)($headR * 0.30), $smileY, $cx + (int)($headR * 0.30), $smileY, col($im, '#7a4a2d'));

    if ($theme === 'dog') {
        $ear = col($im, '#8B5A2B'); $earIn = col($im, '#5c3a1c');
        imagefilledellipse($im, $cx - (int)($headR * 0.95), (int)($headCy - $headR * 0.6), (int)($headR * 0.85), (int)($headR * 1.5), $ear);
        imagefilledellipse($im, $cx + (int)($headR * 0.95), (int)($headCy - $headR * 0.6), (int)($headR * 0.85), (int)($headR * 1.5), $ear);
        imagefilledellipse($im, $cx - (int)($headR * 0.95), (int)($headCy - $headR * 0.2), (int)($headR * 0.4), (int)($headR * 0.8), $earIn);
        imagefilledellipse($im, $cx + (int)($headR * 0.95), (int)($headCy - $headR * 0.2), (int)($headR * 0.4), (int)($headR * 0.8), $earIn);
        imagefilledellipse($im, $cx, $headCy + (int)($headR * 0.20), (int)($headR * 0.55), (int)($headR * 0.45), col($im, '#24150a'));
        imagefilledellipse($im, $cx, $headCy + (int)($headR * 0.17), (int)($headR * 0.22), (int)($headR * 0.13), col($im, '#ffffff'));
        imagefilledellipse($im, $cx - $eyeOff, $eyeY, (int)($headR * 0.24), (int)($headR * 0.24), $eye);
        imagefilledellipse($im, $cx + $eyeOff, $eyeY, (int)($headR * 0.24), (int)($headR * 0.24), $eye);
    } elseif ($theme === 'hearts') {
        drawHeart($im, $cx - $eyeOff, $eyeY, (int)($headR * 0.32), col($im, '#e0245e'));
        drawHeart($im, $cx + $eyeOff, $eyeY, (int)($headR * 0.32), col($im, '#e0245e'));
    } elseif ($theme === 'vintage') {
        imagefilledellipse($im, $cx - $eyeOff, $eyeY, (int)($headR * 0.16), (int)($headR * 0.16), $eye);
        imagefilledellipse($im, $cx + $eyeOff, $eyeY, (int)($headR * 0.16), (int)($headR * 0.16), $eye);
    } elseif ($theme === 'solace') {
        imagefilledellipse($im, $cx - $eyeOff, $eyeY, (int)($headR * 0.22), (int)($headR * 0.22), col($im, '#5b4390'));
        imagefilledellipse($im, $cx + $eyeOff, $eyeY, (int)($headR * 0.22), (int)($headR * 0.22), col($im, '#5b4390'));
        imagefilledellipse($im, $cx - (int)($headR * 0.9), (int)($smileY - $headR * 0.1), (int)($headR * 0.3), (int)($headR * 0.2), col($im, '#c4b5fd'));
        imagefilledellipse($im, $cx + (int)($headR * 0.9), (int)($smileY - $headR * 0.1), (int)($headR * 0.3), (int)($headR * 0.2), col($im, '#c4b5fd'));
    } elseif ($theme === 'classic') {
        imagefilledellipse($im, $cx - $eyeOff, $eyeY - 4, (int)($headR * 0.13), (int)($headR * 0.24), $eye);
        imagefilledellipse($im, $cx + $eyeOff, $eyeY - 4, (int)($headR * 0.13), (int)($headR * 0.24), $eye);
    } elseif ($theme === 'with_love') {
        drawHeart($im, $cx, $smileY + (int)($headR * 0.35), (int)($headR * 0.34), col($im, '#e0245e'));
        imagefilledellipse($im, $cx - $eyeOff, $eyeY, (int)($headR * 0.16), (int)($headR * 0.16), $eye);
        imagefilledellipse($im, $cx + $eyeOff, $eyeY, (int)($headR * 0.16), (int)($headR * 0.16), $eye);
    } elseif ($theme === 'holidays') {
        imagestring($im, 4, (int)($cx - 8), (int)($headCy - $headR - 12), '*', col($im, '#38bdf8'));
        imagefilledellipse($im, $cx - $eyeOff, $eyeY, (int)($headR * 0.16), (int)($headR * 0.16), $eye);
        imagefilledellipse($im, $cx + $eyeOff, $eyeY, (int)($headR * 0.16), (int)($headR * 0.16), $eye);
    } else {
        // default plain / strips / grid / polaroid
        imagefilledellipse($im, $cx - $eyeOff, $eyeY, (int)($headR * 0.17), (int)($headR * 0.17), $eye);
        imagefilledellipse($im, $cx + $eyeOff, $eyeY, (int)($headR * 0.17), (int)($headR * 0.17), $eye);
    }
}

foreach ($defs as [$id, $W, $H, $cols, $rows, $label]) {
    $theme = in_array($id, $themedIds) ? $id : null;
    $polaroid = $id === 'polaroid';
    $n = $cols * $rows;

    $im = imagecreatetruecolor($W, $H);
    imagefill($im, 0, 0, col($im, '#ffffff'));

    // margin/gap proporsional ke ukuran
    $margin = $W >= 600 ? 30 : 24;
    $gap = 14;
    $pals = [['#ffe4c9', '#f6a8a0'], ['#cfe4ff', '#8da8f0'], ['#d5f8e3', '#7fcfa4'], ['#f2d9f7', '#c79af0']];

    if ($polaroid) {
        $pw = $W - $margin * 2 - 30; $ph = $pw;
        $px = (int)(($W - $pw) / 2); $py = $margin;
        vgrad($im, $px, $py, $pw, $ph, $pals[0][0], $pals[0][1]);
        drawPortrait($im, $px, $py, $pw, $ph, null, 0);
        imagerectangle($im, $px, $py, $px + $pw, $py + $ph, col($im, '#ffffff'));
        // polaroid bottom area (label + date)
        $label = 'POLAROID';
    } else {
        // auto size foto merata
        $fw = $W - $margin * 2 - $gap * ($cols - 1);
        $pw = intdiv($fw, $cols);
        $usableH = $H - $margin * 2 - $gap * ($rows - 1) - ($W < 600 ? 96 : 60);
        $ph = intdiv($usableH, $rows);
        for ($i = 0; $i < $n; $i++) {
            $c = $i % $cols; $r = intdiv($i, $cols);
            $x = $margin + $c * ($pw + $gap);
            $y = $margin + $r * ($ph + $gap);
            vgrad($im, $x, $y, $pw, $ph, $pals[$i % 4][0], $pals[$i % 4][1]);
            drawPortrait($im, $x, $y, $pw, $ph, $theme, $i);
            imagerectangle($im, $x, $y, $x + $pw, $y + $ph, col($im, '#ffffff'));
            imagerectangle($im, $x + 2, $y + 2, $x + $pw - 2, $y + $ph - 2, col($im, '#000000', 12));
        }
    }

    // border luar
    imagerectangle($im, 6, 6, $W - 7, $H - 7, col($im, '#e2e8f0'));
    imagerectangle($im, 3, 3, $W - 4, $H - 4, col($im, '#f1f5f9'));

    // decor side per tema
    $accentCol = col($im, '#e0245e');
    foreach (['hearts' => $accentCol, 'with_love' => $accentCol, 'dog' => col($im, '#8B5A2B'), 'solace' => col($im, '#a5b4fc'), 'holidays' => col($im, '#facc15'), 'classic' => col($im, '#475569')] as $tid => $tcol) {
        if ($id === $tid) {
            for ($i = 0; $i < 8; $i++) {
                $dy = 40 + $i * (($H - 80) / 8);
                if ($tid === 'hearts' || $tid === 'with_love') { drawHeart($im, 12, (int)$dy, 6, $tcol); drawHeart($im, $W - 12, (int)$dy, 6, $tcol); }
                else { imagefilledellipse($im, 12, (int)$dy, 9, 9, $tcol); imagefilledellipse($im, $W - 12, (int)$dy, 9, 9, $tcol); }
            }
        }
    }

    // footer
    $sizeLabel = $cols > 1 ? "Size " . ($W >= 600 ? "6 x 4" : "6 x 4") . " Strip  (" . $n . " Pose)"
                           : ($polaroid ? "POLAROID" : "Size 6 x 2 Strip  (" . $n . " Pose)");
    $brandU = strtoupper($label);
    $fg = imagecolorallocate($im, 30, 41, 59);
    $fgSoft = imagecolorallocate($im, 100, 116, 139);
    $footY = $H - ($W < 600 ? 90 : 56);
    imagestring($im, 3, (int)($W/2) - (int)(strlen($brandU) * 4.2), $footY - 12, $brandU, $fg);
    imagestring($im, 2, (int)($W/2) - (int)(strlen($sizeLabel) * 3.4), $footY + 10, $sizeLabel, $fgSoft);
    imagestring($im, 1, (int)($W/2) - 18, $footY + 32, date('d M Y'), $fgSoft);

    imagepng($im, $outDir . '/' . $id . '.png');
    imagedestroy($im);
    echo "Generated: " . $id . ".png ({$W}x{$H})\n";
}

echo "Selesai. " . count($defs) . " preview dibuat di public/layout-previews/\n";
