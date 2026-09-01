<?php
// Generate AR dog-filter assets (ears + nose) as transparent PNGs.
// Drawn with GD curves so no external assets are copied.

function nCr(int $n, int $r): int {
    $res = 1;
    for ($i = 0; $i < $r; $i++) {
        $res = $res * ($n - $i) / ($i + 1);
    }
    return (int) $res;
}

function bezier(float $t, array $p) {
    $n = count($p) - 1;
    $x = $y = 0.0;
    for ($i = 0; $i <= $n; $i++) {
        $c = nCr($n, $i);
        $pow = pow(1 - $t, $n - $i) * pow($t, $i);
        $x += $c * $pow * $p[$i][0];
        $y += $c * $pow * $p[$i][1];
    }
    return [$x, $y];
}

function fillPath($img, array $ctrl, $r, $g, $b, $a = 0) {
    $pts = [];
    for ($i = 0; $i <= 60; $i++) {
        [$x, $y] = bezier($i / 60, $ctrl);
        $pts[] = [$x, $y];
    }
    // closed polygon
    for ($i = 60 - 1; $i >= 0; $i--) {
        $pts[] = $ctrl[count($ctrl) - 1];
        break;
    }
    $poly = [];
    foreach ($pts as $p) { $poly[] = round($p[0]); $poly[] = round($p[1]); }
    $color = imagecolorallocatealpha($img, $r, $g, $b, $a);
    imagefilledpolygon($img, $poly, $color);
}

// ----------------- EARS -----------------
// Di-MM: anchor dahi = -earsH*0.55 dari top image, jadi "pangkalan" telinga
// harus duduk di sekitar 55-60% tinggi gambar, dan telinga menggantung ke
// BAWAH-SISI (bukan memenuhi tengah). Cek: identik kebalikan photobooth-io
// dog-ears (pair of floppy ears with transparent gap between them).
$W = 300; $H = 210;
$img = imagecreatetruecolor($W, $H);
imagealphablending($img, false);
imagesavealpha($img, true);
$trans = imagecolorallocatealpha($img, 0, 0, 0, 127);
imagefilledrectangle($img, 0, 0, $W, $H, $trans);
imagealphablending($img, true);

$brown =   [91, 58, 30];    // #5B3A1E
$outline = [64, 40, 20];
$pink =    [232, 160, 160]; // #E8A0A0

function earLobe($img, $cx, $cy, $dir, $outer, $inner) {
    // Lean ear ke luar-samping dari (cx,cy) (pangkal menempel di kepala),
    // menggantung ke bawah; kembali ke pangkal atas.
    $scale = 1.0;
    $ctrl = [
        [$cx,             $cy],                 // pangkal-atas (menempel kepala)
        [$cx - 14 * $dir, $cy + 34],
        [$cx - 44 * $dir, $cy + 76],
        [$cx - 62 * $dir * $scale, $cy + 128],
        [$cx - 40 * $dir * $scale, $cy + 168],
        [$cx - 2  * $dir * $scale, $cy + 172],
        [$cx + 26 * $dir * $scale, $cy + 148],
        [$cx + 40 * $dir, $cy + 92],
        [$cx + 30 * $dir, $cy + 40],
        [$cx + 10 * $dir, $cy],
        [$cx,             $cy],
    ];
    fillPath($img, $ctrl, $outer[0], $outer[1], $outer[2]);
    // inner pink (lebih kecil)
    $ctrl2 = [
        [$cx - 2  * $dir, $cy + 26],
        [$cx - 22 * $dir, $cy + 60],
        [$cx - 34 * $dir * $scale, $cy + 104],
        [$cx - 26 * $dir * $scale, $cy + 136],
        [$cx - 8  * $dir * $scale, $cy + 148],
        [$cx + 10 * $dir * $scale, $cy + 128],
        [$cx + 16 * $dir, $cy + 88],
        [$cx + 10 * $dir, $cy + 44],
        [$cx - 2  * $dir, $cy + 26],
    ];
    fillPath($img, $ctrl2, $inner[0], $inner[1], $inner[2]);
}

// Pangkal telinga di tepi kepala (≈ 25% dan 75% lebar), duduk di y≈55-58%
// dari tinggi (titik anchor -earsH*0.55), menggantung ke bawah-samping.
// Celah tengah (x 120..180) DIBIARKAN TRANSPARAN agar dahi tetap terlihat.
earLobe($img, 70, 118, 1, $outline, $brown);
earLobe($img, 76, 124, 1, $brown, $pink);
earLobe($img, $W - 70, 118, -1, $outline, $brown);
earLobe($img, $W - 76, 124, -1, $brown, $pink);

imagepng($img, __DIR__ . '/../public/ar/dog-ears.png');
imagedestroy($img);

// ----------------- NOSE -----------------
// Anchor: -noseH*0.53 → konten (pad hidung) harus duduk di sekitar 50-60%
// tinggi gambar supaya pas menutupi ujung hidung asli + pangkal moncong.
$W = 240; $H = 170;
$img = imagecreatetruecolor($W, $H);
imagealphablending($img, false);
imagesavealpha($img, true);
$trans = imagecolorallocatealpha($img, 0, 0, 0, 127);
imagefilledrectangle($img, 0, 0, $W, $H, $trans);
imagealphablending($img, true);

// muzzle base (soft cream) — ellipse section
$cream = [254, 247, 236];
$shadow = [235, 220, 200];
$dark = [43, 26, 16];   // nose dark
$nozzle = [110, 70, 45];
$highlight = [255, 255, 255];

// Pangkal moncong (bagian atas, menyatu ke wajah) — letakkan di y≈70
imagefilledellipse($img, 120, 128, 200, 96, imagecolorallocatealpha($img, $shadow[0], $shadow[1], $shadow[2], 0));
imagefilledellipse($img, 120, 122, 200, 92, imagecolorallocatealpha($img, $cream[0], $cream[1], $cream[2], 0));

// nose pad (two-lobe bean) — tepat di ujung hidung (cy≈53% tinggi gambar
// supaya anchor -noseH*0.53 menempatkan pad tepat di noseTip)
imagefilledellipse($img, 120, 90, 110, 62, imagecolorallocatealpha($img, $dark[0], $dark[1], $dark[2], 0));
imagefilledellipse($img, 92, 103, 52, 40, imagecolorallocatealpha($img, $nozzle[0], $nozzle[1], $nozzle[2], 0));
imagefilledellipse($img, 148, 103, 52, 40, imagecolorallocatealpha($img, $nozzle[0], $nozzle[1], $nozzle[2], 0));
imagefilledellipse($img, 120, 82, 30, 14, imagecolorallocatealpha($img, $highlight[0], $highlight[1], $highlight[2], 0));

// vertical philtrum slit
imagesetthickness($img, 3);
imageline($img, 120, 118, 120, 128, imagecolorallocatealpha($img, $dark[0], $dark[1], $dark[2], 0));
// two mouth corners curve
imagesetthickness($img, 2);
imageline($img, 96, 138, 120, 124, imagecolorallocatealpha($img, $dark[0], $dark[1], $dark[2], 0));
imageline($img, 144, 138, 120, 124, imagecolorallocatealpha($img, $dark[0], $dark[1], $dark[2], 0));

imagepng($img, __DIR__ . '/../public/ar/dog-nose.png');
imagedestroy($img);

echo "Assets written:\n";
foreach (['dog-ears.png', 'dog-nose.png'] as $f) {
    $p = __DIR__ . '/../public/ar/' . $f;
    if (file_exists($p)) {
        $i = getimagesize($p);
        echo "  public/ar/{$f} -> {$i[0]}x{$i[1]}, " . filesize($p) . " bytes\n";
    } else {
        echo "  MISSING public/ar/{$f}\n";
    }
}