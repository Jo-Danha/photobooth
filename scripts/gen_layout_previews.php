<?php
/**
 * Generate preview PNG statis untuk halaman "Pilih Format & Layout" DAN panel admin.
 * Tujuan: preview tampak seperti CONTOH FOTO JADI — hidup, berwarna, punya karakter,
 * bukan kotak kosong. Setiap layout menampilkan wajah-wajah dengan ekspresi berbeda
 * plus efek AR (telinga/nose/kacamata) yang cocok dengan tema layout-nya.
 *
 * Output: public/layout-previews/{layout_id}.png
 * Jalankan: php scripts/gen_layout_previews.php
 */

if (!extension_loaded('gd')) { echo "GD tidak tersedia\n"; exit(1); }

$outDir = __DIR__ . '/../public/layout-previews';
if (!is_dir($outDir)) mkdir($outDir, 0775, true);

// warna kulit & baju untuk tiap orang, supaya tiap foto beda karakter
$people = [
    ['skin' => '#c99778', 'shirt' => '#ef4444', 'hair' => '#35230f'],
    ['skin' => '#e7b08e', 'shirt' => '#3b82f6', 'hair' => '#1c150e'],
    ['skin' => '#8a5a3b', 'shirt' => '#10b981', 'hair' => '#0d0d0d'],
    ['skin' => '#f3cdb4', 'shirt' => '#f59e0b', 'hair' => '#7c4a21'],
];

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
function drawStar($im, $cx, $cy, $r, $c) {
    $pts = [];
    for ($k = 0; $k < 10; $k++) {
        $ang = M_PI / 2 + $k * M_PI / 5;
        $rad = ($k % 2 === 0) ? $r : $r * 0.4;
        $pts[] = $cx + cos($ang) * $rad;
        $pts[] = $cy - sin($ang) * $rad;
    }
    imagefilledpolygon($im, array_map('intval', $pts), 5, $c);
}
// telinga kelinci / rubah / anjing / kucing (segitiga atau lonjong)
function drawEar($im, $cx, $cy, $w, $h, $fill, $inner = null, $triangle = false) {
    if ($triangle) {
        imagefilledpolygon($im, [(int)$cx - (int)($w/2), (int)$cy, (int)$cx, (int)($cy - $h), (int)$cx + (int)($w/2), (int)$cy], 3, $fill);
        if ($inner) imagefilledpolygon($im, [(int)$cx - (int)($w/4), (int)$cy + 2, (int)$cx, (int)($cy - $h * 0.6), (int)$cx + (int)($w/4), (int)$cy + 2], 3, $inner);
    } else {
        imagefilledellipse($im, $cx, (int)($cy - $h/2), $w, $h, $fill);
        if ($inner) imagefilledellipse($im, $cx, (int)($cy - $h/2), (int)($w * 0.55), (int)($h * 0.7), $inner);
    }
}
// kacamata hitam
function drawShades($im, $cx, $cy, $w, $h, $eyeOff, $c) {
    foreach ([-1, 1] as $dir) {
        imagefilledellipse($im, $cx + $dir * $eyeOff, $cy, (int)($w * 0.62), (int)($h * 0.55), $c);
    }
    imageline($im, $cx - (int)($eyeOff * 0.55), $cy, $cx + (int)($eyeOff * 0.55), $cy, $c);
}
// gambar satu wajah ceria dengan pose & filter sesuai tema
function drawPortrait($im, $x, $y, $w, $h, $theme, $i) {
    $cx = (int)($x + $w / 2);
    $headR = (int)($h * 0.21);
    $headCy = (int)($y + $h * 0.36);
    $p = $GLOBALS['people'][$i % 4];
    $skinCol = col($im, $p['skin']);
    $dish = (int)($headR * 0.16);
    // bahu & baju (lebih tinggi, membentuk)
    imagefilledellipse($im, (int)($cx - $w * 0.18), (int)($y + $h * 1.02), (int)($w * 0.62), (int)($h * 0.5), col($im, $p['shirt']));
    imagefilledellipse($im, (int)($cx + $w * 0.18), (int)($y + $h * 1.02), (int)($w * 0.62), (int)($h * 0.5), col($im, $p['shirt']));
    imagefilledellipse($im, $cx, (int)($y + $h * 0.95), (int)($w * 0.55), (int)($h * 0.42), col($im, $p['shirt']));
    // leher
    imagefilledrectangle($im, $cx - (int)($w * 0.08), $headCy + (int)($headR), $cx + (int)($w * 0.08), $headCy + (int)($headR * 1.5), $skinCol);
    // kepala (agak oval)
    imagefilledellipse($im, $cx, $headCy, (int)($headR * 2.0), (int)($headR * 2.15), $skinCol);
    // rambut di atas
    imagefilledarc($im, $cx, $headCy - 2, (int)($headR * 2.05), (int)($headR * 2.2), 180, 360, col($im, $p['hair']), IMG_ARC_PIE);
    imagefilledellipse($im, $cx, (int)($headCy - $headR * 0.85), (int)($headR * 2.05), (int)($headR * 0.55), col($im, $p['hair']));
    // blush pipi
    imagefilledellipse($im, (int)($cx - $headR * 0.72), (int)($headCy + $headR * 0.18), (int)($headR * 0.32), (int)($headR * 0.2), col($im, '#f39aa8', 30));
    imagefilledellipse($im, (int)($cx + $headR * 0.72), (int)($headCy + $headR * 0.18), (int)($headR * 0.32), (int)($headR * 0.2), col($im, '#f39aa8', 30));

    $eyeY = (int)($headCy - $headR * 0.08);
    $eyeOff = (int)($headR * 0.48);
    $smileY = (int)($headCy + $headR * 0.42);
    $line = col($im, '#7a4a2d');
    $white = col($im, '#ffffff');
    $dark = col($im, '#35230f');

    // variasi ekspresi berdasarkan i: senyum beragam
    $style = $i % 4;
    imagesetthickness($im, 2);
    if ($style === 0) {
        // senyum lebar (busur)
        imagearc($im, $cx, $smileY, (int)($headR * 0.55), (int)($headR * 0.34), 0, 180, $line);
    } elseif ($style === 1) {
        // mulut kecil bulat "o"
        imagefilledellipse($im, $cx, $smileY + 2, (int)($headR * 0.18), (int)($headR * 0.22), col($im, '#7a4a2d'));
    } elseif ($style === 2) {
        // senyum simpul
        imageline($im, $cx - (int)($headR * 0.22), $smileY - 3, $cx - (int)($headR * 0.05), $smileY + 3, $line);
        imageline($im, $cx + (int)($headR * 0.22), $smileY - 3, $cx + (int)($headR * 0.05), $smileY + 3, $line);
        imageline($im, $cx - (int)($headR * 0.10), $smileY - 6, $cx + (int)($headR * 0.10), $smileY - 6, $line);
    } else {
        // senyum dengan gigi
        imagefilledellipse($im, $cx, $smileY + 2, (int)($headR * 0.5), (int)($headR * 0.3), $dark);
        imagefilledrectangle($im, $cx - (int)($headR * 0.12), $smileY - 4, $cx + (int)($headR * 0.12), $smileY + 2, $white);
    }
    imagesetthickness($im, 1);

    // ---- FILTER AR per tema (persis yang terjadi di studio saat live) ----
    if ($theme === 'dog') {
        $ear = col($im, '#8B5A2B'); $earIn = col($im, '#5c3a1c');
        imagefilledellipse($im, (int)($cx - $headR * 0.92), (int)($headCy - $headR * 0.7), (int)($headR * 0.82), (int)($headR * 1.5), $ear);
        imagefilledellipse($im, (int)($cx + $headR * 0.92), (int)($headCy - $headR * 0.7), (int)($headR * 0.82), (int)($headR * 1.5), $ear);
        imagefilledellipse($im, (int)($cx - $headR * 0.92), (int)($headCy - $headR * 0.25), (int)($headR * 0.4), (int)($headR * 0.8), $earIn);
        imagefilledellipse($im, (int)($cx + $headR * 0.92), (int)($headCy - $headR * 0.25), (int)($headR * 0.4), (int)($headR * 0.8), $earIn);
        imagefilledellipse($im, $cx, (int)($headCy + $headR * 0.2), (int)($headR * 0.55), (int)($headR * 0.45), col($im, '#24150a'));
        imagefilledellipse($im, $cx, (int)($headCy + $headR * 0.17), (int)($headR * 0.2), (int)($headR * 0.12), $white);
        // lidah
        imagefilledellipse($im, $cx, (int)($smileY + $headR * 0.16), (int)($headR * 0.2), (int)($headR * 0.3), col($im, '#e84393'));
        imagefilledellipse($im, (int)($cx - $eyeOff), $eyeY, (int)($headR * 0.22), (int)($headR * 0.22), $dark);
        imagefilledellipse($im, (int)($cx + $eyeOff), $eyeY, (int)($headR * 0.22), (int)($headR * 0.22), $dark);
        imagefilledellipse($im, (int)($cx - $eyeOff + $headR * 0.07), (int)($eyeY - $headR * 0.06), (int)($headR * 0.08), (int)($headR * 0.08), $white);
        imagefilledellipse($im, (int)($cx + $eyeOff + $headR * 0.07), (int)($eyeY - $headR * 0.06), (int)($headR * 0.08), (int)($headR * 0.08), $white);
    } elseif ($theme === 'cat') {
        $ear = col($im, '#3b2a1e'); $earIn = col($im, '#f1a7b8');
        drawEar($im, (int)($cx - $headR * 0.85), (int)($headCy - $headR * 1.05), (int)($headR * 0.6), (int)($headR * 0.85), $ear, $earIn, true);
        drawEar($im, (int)($cx + $headR * 0.85), (int)($headCy - $headR * 1.05), (int)($headR * 0.6), (int)($headR * 0.85), $ear, $earIn, true);
        // kumis
        $ny = (int)($headCy + $headR * 0.22);
        foreach ([-1, 1] as $dir) {
            for ($k = -1; $k <= 1; $k++) {
                $wy = $ny + $k * (int)($headR * 0.16);
                imageline($im, $cx + $dir * (int)($headR * 0.28), $wy, $cx + $dir * (int)($headR * 0.8), (int)($wy - $k * $headR * 0.04), col($im, '#35230f'));
            }
        }
        // hidung + mulut
        imagefilledpolygon($im, [(int)($cx - $headR*0.14), (int)($headCy + $headR*0.18), (int)($cx + $headR*0.14), (int)($headCy + $headR*0.18), $cx, (int)($headCy + $headR*0.27)], 3, col($im, '#e76f51'));
        imageline($im, $cx, (int)($headCy + $headR * 0.27), $cx, (int)($headCy + $headR * 0.38), col($im, '#35230f'));
        imageline($im, $cx, (int)($headCy + $headR * 0.38), $cx - (int)($headR * 0.14), (int)($headCy + $headR * 0.42), col($im, '#35230f'));
        imageline($im, $cx, (int)($headCy + $headR * 0.38), $cx + (int)($headR * 0.14), (int)($headCy + $headR * 0.42), col($im, '#35230f'));
        imagefilledellipse($im, (int)($cx - $eyeOff), $eyeY, (int)($headR * 0.24), (int)($headR * 0.24), $dark);
        imagefilledellipse($im, (int)($cx + $eyeOff), $eyeY, (int)($headR * 0.24), (int)($headR * 0.24), $dark);
    } elseif ($theme === 'bunny') {
        $ear = col($im, '#f7f3ef'); $earIn = col($im, '#f9c5d5');
        drawEar($im, (int)($cx - $headR * 0.72), (int)($headCy - $headR * 1.25), (int)($headR * 0.38), (int)($headR * 1.4), $ear, $earIn, false);
        drawEar($im, (int)($cx + $headR * 0.72), (int)($headCy - $headR * 1.25), (int)($headR * 0.38), (int)($headR * 1.4), $ear, $earIn, false);
        // hidung + gigi
        imagefilledpolygon($im, [(int)($cx - $headR*0.14), (int)($headCy + $headR*0.16), (int)($cx + $headR*0.14), (int)($headCy + $headR*0.16), $cx, (int)($headCy + $headR*0.26)], 3, col($im, '#f284a8'));
        imagefilledrectangle($im, (int)($cx - $headR*0.06), (int)($headCy + $headR*0.26), (int)($cx + $headR*0.06), (int)($headCy + $headR*0.38), $white);
        imagefilledellipse($im, (int)($cx - $eyeOff), $eyeY, (int)($headR * 0.2), (int)($headR * 0.2), $dark);
        imagefilledellipse($im, (int)($cx + $eyeOff), $eyeY, (int)($headR * 0.2), (int)($headR * 0.2), $dark);
    } elseif ($theme === 'fox') {
        $ear = col($im, '#e8833a'); $earIn = col($im, '#fbf0e4');
        drawEar($im, (int)($cx - $headR * 0.85), (int)($headCy - $headR * 1.05), (int)($headR * 0.62), (int)($headR * 0.9), $ear, $earIn, true);
        drawEar($im, (int)($cx + $headR * 0.85), (int)($headCy - $headR * 1.05), (int)($headR * 0.62), (int)($headR * 0.9), $ear, $earIn, true);
        // moncong bawah putih
        imagefilledellipse($im, $cx, (int)($headCy + $headR * 0.30), (int)($headR * 0.6), (int)($headR * 0.45), $white);
        imagefilledellipse($im, $cx, (int)($headCy + $headR * 0.18), (int)($headR * 0.16), (int)($headR * 0.13), col($im, '#2a2019'));
        imageline($im, $cx, (int)($headCy + $headR * 0.35), $cx, (int)($headCy + $headR * 0.48), col($im, '#2a2019'));
        imageline($im, $cx, (int)($headCy + $headR * 0.48), $cx - (int)($headR * 0.12), (int)($headCy + $headR * 0.52), col($im, '#2a2019'));
        imageline($im, $cx, (int)($headCy + $headR * 0.48), $cx + (int)($headR * 0.12), (int)($headCy + $headR * 0.52), col($im, '#2a2019'));
        imagefilledellipse($im, (int)($cx - $eyeOff), $eyeY, (int)($headR * 0.2), (int)($headR * 0.2), $dark);
        imagefilledellipse($im, (int)($cx + $eyeOff), $eyeY, (int)($headR * 0.2), (int)($headR * 0.2), $dark);
    } elseif ($theme === 'cool') {
        drawShades($im, $cx, $eyeY, (int)($headR * 0.5), (int)($headR * 0.5), $eyeOff, col($im, '#14141c'));
        // kilau
        imagefilledellipse($im, (int)($cx - $eyeOff - $headR * 0.12), $eyeY - (int)($headR * 0.1), (int)($headR * 0.14), (int)($headR * 0.08), col($im, '#ffffff'));
        imagefilledellipse($im, (int)($cx + $eyeOff - $headR * 0.12), $eyeY - (int)($headR * 0.1), (int)($headR * 0.14), (int)($headR * 0.08), col($im, '#ffffff'));
        // senyum dingin + bintang kecil
        imagearc($im, $cx, $smileY, (int)($headR * 0.5), (int)($headR * 0.28), 0, 180, $line);
        drawStar($im, (int)($cx - $headR * 1.0), (int)($headCy - $headR * 0.4), (int)($headR * 0.28), col($im, '#facc15'));
        drawStar($im, (int)($cx + $headR * 1.05), (int)($headCy + $headR * 0.1), (int)($headR * 0.2), col($im, '#f472b6'));
    } elseif ($theme === 'hearts') {
        drawHeart($im, (int)($cx - $eyeOff), $eyeY, (int)($headR * 0.32), col($im, '#e0245e'));
        drawHeart($im, (int)($cx + $eyeOff), $eyeY, (int)($headR * 0.32), col($im, '#e0245e'));
    } elseif ($theme === 'vintage') {
        // wajah sepia klasik + kacamata bulat
        $glass = col($im, '#5b4636');
        imagearc($im, (int)($cx - $headR * 0.5), $eyeY, (int)($headR * 0.34), (int)($headR * 0.34), 0, 360, $glass);
        imagearc($im, (int)($cx + $headR * 0.5), $eyeY, (int)($headR * 0.34), (int)($headR * 0.34), 0, 360, $glass);
        imageline($im, (int)($cx - $headR * 0.5), $eyeY, (int)($cx + $headR * 0.5), $eyeY, $glass);
    } elseif ($theme === 'solace') {
        imagefilledellipse($im, (int)($cx - $eyeOff), $eyeY, (int)($headR * 0.24), (int)($headR * 0.24), col($im, '#5b4390'));
        imagefilledellipse($im, (int)($cx + $eyeOff), $eyeY, (int)($headR * 0.24), (int)($headR * 0.24), col($im, '#5b4390'));
        drawStar($im, (int)($cx - $headR * 0.95), (int)($smileY - $headR * 0.15), (int)($headR * 0.26), col($im, '#c4b5fd'));
        drawStar($im, (int)($cx + $headR * 0.95), (int)($smileY - $headR * 0.15), (int)($headR * 0.26), col($im, '#c4b5fd'));
    } elseif ($theme === 'classic') {
        imagefilledellipse($im, (int)($cx - $eyeOff), $eyeY - 4, (int)($headR * 0.16), (int)($headR * 0.3), $dark);
        imagefilledellipse($im, (int)($cx + $eyeOff), $eyeY - 4, (int)($headR * 0.16), (int)($headR * 0.3), $dark);
    } elseif ($theme === 'with_love') {
        drawHeart($im, $cx, (int)($smileY + $headR * 0.40), (int)($headR * 0.34), col($im, '#e0245e'));
        imagefilledellipse($im, (int)($cx - $eyeOff), $eyeY, (int)($headR * 0.18), (int)($headR * 0.18), $dark);
        imagefilledellipse($im, (int)($cx + $eyeOff), $eyeY, (int)($headR * 0.18), (int)($headR * 0.18), $dark);
    } elseif ($theme === 'holidays') {
        drawStar($im, (int)($cx - $headR * 0.9), (int)($headCy - $headR * 0.35), 9, col($im, '#38bdf8'));
        drawStar($im, (int)($cx + $headR * 0.9), (int)($headCy - $headR * 0.35), 9, col($im, '#34d399'));
        // topi santa-natal
        imagefilledellipse($im, (int)($cx + $headR * 0.85), (int)($headCy - $headR * 1.0), (int)($headR * 0.5), (int)($headR * 0.5), col($im, '#ffffff'));
        imagefilledellipse($im, (int)($cx - $eyeOff), $eyeY, (int)($headR * 0.16), (int)($headR * 0.16), $dark);
        imagefilledellipse($im, (int)($cx + $eyeOff), $eyeY, (int)($headR * 0.16), (int)($headR * 0.16), $dark);
    } elseif ($theme === 'ar') {
        // Layout AR terpadu: tiap pose memakai FILTER yang berbeda (dog, cat, bunny, fox, cool)
        $pick = ['dog', 'cat', 'bunny', 'fox', 'cool'];
        $t = $pick[$i % count($pick)];
        $ear = col($im, '#8B5A2B'); $earIn = col($im, '#5c3a1c');
        if ($t === 'dog') {
            imagefilledellipse($im, (int)($cx - $headR * 0.92), (int)($headCy - $headR * 0.7), (int)($headR * 0.82), (int)($headR * 1.5), $ear);
            imagefilledellipse($im, (int)($cx + $headR * 0.92), (int)($headCy - $headR * 0.7), (int)($headR * 0.82), (int)($headR * 1.5), $ear);
            imagefilledellipse($im, (int)($cx - $headR * 0.92), (int)($headCy - $headR * 0.25), (int)($headR * 0.4), (int)($headR * 0.8), $earIn);
            imagefilledellipse($im, (int)($cx + $headR * 0.92), (int)($headCy - $headR * 0.25), (int)($headR * 0.4), (int)($headR * 0.8), $earIn);
            imagefilledellipse($im, $cx, (int)($headCy + $headR * 0.2), (int)($headR * 0.55), (int)($headR * 0.45), col($im, '#24150a'));
            imagefilledellipse($im, $cx, (int)($headCy + $headR * 0.17), (int)($headR * 0.2), (int)($headR * 0.12), $white);
        } elseif ($t === 'cat') {
            $ear = col($im, '#3b2a1e'); $earIn = col($im, '#f1a7b8');
            drawEar($im, (int)($cx - $headR * 0.85), (int)($headCy - $headR * 1.05), (int)($headR * 0.6), (int)($headR * 0.85), $ear, $earIn, true);
            drawEar($im, (int)($cx + $headR * 0.85), (int)($headCy - $headR * 1.05), (int)($headR * 0.6), (int)($headR * 0.85), $ear, $earIn, true);
            $ny = (int)($headCy + $headR * 0.22);
            foreach ([-1, 1] as $dir) {
                for ($k = -1; $k <= 1; $k++) {
                    $wy = $ny + $k * (int)($headR * 0.16);
                    imageline($im, $cx + $dir * (int)($headR * 0.28), $wy, $cx + $dir * (int)($headR * 0.8), (int)($wy - $k * $headR * 0.04), col($im, '#35230f'));
                }
            }
            imagefilledpolygon($im, [(int)($cx - $headR*0.14), (int)($headCy + $headR*0.18), (int)($cx + $headR*0.14), (int)($headCy + $headR*0.18), $cx, (int)($headCy + $headR*0.27)], 3, col($im, '#e76f51'));
        } elseif ($t === 'bunny') {
            $ear = col($im, '#f7f3ef'); $earIn = col($im, '#f9c5d5');
            drawEar($im, (int)($cx - $headR * 0.72), (int)($headCy - $headR * 1.25), (int)($headR * 0.38), (int)($headR * 1.4), $ear, $earIn, false);
            drawEar($im, (int)($cx + $headR * 0.72), (int)($headCy - $headR * 1.25), (int)($headR * 0.38), (int)($headR * 1.4), $ear, $earIn, false);
            imagefilledpolygon($im, [(int)($cx - $headR*0.14), (int)($headCy + $headR*0.16), (int)($cx + $headR*0.14), (int)($headCy + $headR*0.16), $cx, (int)($headCy + $headR*0.26)], 3, col($im, '#f284a8'));
        } elseif ($t === 'fox') {
            $ear = col($im, '#e8833a'); $earIn = col($im, '#fbf0e4');
            drawEar($im, (int)($cx - $headR * 0.85), (int)($headCy - $headR * 1.05), (int)($headR * 0.62), (int)($headR * 0.9), $ear, $earIn, true);
            drawEar($im, (int)($cx + $headR * 0.85), (int)($headCy - $headR * 1.05), (int)($headR * 0.62), (int)($headR * 0.9), $ear, $earIn, true);
            imagefilledellipse($im, $cx, (int)($headCy + $headR * 0.30), (int)($headR * 0.6), (int)($headR * 0.45), $white);
            imagefilledellipse($im, $cx, (int)($headCy + $headR * 0.18), (int)($headR * 0.16), (int)($headR * 0.13), col($im, '#2a2019'));
        } else {
            drawShades($im, $cx, $eyeY, (int)($headR * 0.5), (int)($headR * 0.5), $eyeOff, col($im, '#14141c'));
            imagefilledellipse($im, (int)($cx - $eyeOff - $headR * 0.12), $eyeY - (int)($headR * 0.1), (int)($headR * 0.14), (int)($headR * 0.08), col($im, '#ffffff'));
            imagefilledellipse($im, (int)($cx + $eyeOff - $headR * 0.12), $eyeY - (int)($headR * 0.1), (int)($headR * 0.14), (int)($headR * 0.08), col($im, '#ffffff'));
        }
        imagefilledellipse($im, (int)($cx - $eyeOff), $eyeY, (int)($headR * 0.2), (int)($headR * 0.2), $dark);
        imagefilledellipse($im, (int)($cx + $eyeOff), $eyeY, (int)($headR * 0.2), (int)($headR * 0.2), $dark);
        imagefilledellipse($im, (int)($cx - $eyeOff + $headR * 0.06), (int)($eyeY - $headR * 0.05), (int)($headR * 0.07), (int)($headR * 0.07), $white);
        imagefilledellipse($im, (int)($cx + $eyeOff + $headR * 0.06), (int)($eyeY - $headR * 0.05), (int)($headR * 0.07), (int)($headR * 0.07), $white);
    } else {
        imagefilledellipse($im, (int)($cx - $eyeOff), $eyeY, (int)($headR * 0.2), (int)($headR * 0.2), $dark);
        imagefilledellipse($im, (int)($cx + $eyeOff), $eyeY, (int)($headR * 0.2), (int)($headR * 0.2), $dark);
        imagefilledellipse($im, (int)($cx - $eyeOff + $headR * 0.06), (int)($eyeY - $headR * 0.05), (int)($headR * 0.07), (int)($headR * 0.07), $white);
        imagefilledellipse($im, (int)($cx + $eyeOff + $headR * 0.06), (int)($eyeY - $headR * 0.05), (int)($headR * 0.07), (int)($headR * 0.07), $white);
    }
}

// ================= BAGIAN UTAMA =================
$defs = [
    ['strip_4',   400, 1164, 1, 4, 'Layout B'],
    ['strip_3',   400,  906, 1, 3, 'Layout A'],
    ['strip_2',   400,  648, 1, 2, 'Layout C'],
    ['strip_6',   678,  861, 2, 3, 'Layout D'],
    ['strip_e',   678,  618, 2, 2, 'Layout E'],
    ['grid_4',    678,  618, 2, 2, 'GRID 4'],
    ['polaroid',  420,  500, 1, 1, 'POLAROID'],
    ['ar',        400, 1164, 1, 4, 'AR FILTERS'],
    ['hearts',    400, 1164, 1, 4, 'HEARTS'],
    ['dog',       400, 1164, 1, 4, 'DOG'],
    ['cat',       400, 1164, 1, 4, 'CAT'],
    ['bunny',     400, 1164, 1, 4, 'BUNNY'],
    ['fox',       400, 1164, 1, 4, 'FOX'],
    ['cool',      400, 1164, 1, 4, 'COOL'],
    ['vintage',   400, 1164, 1, 4, 'VINTAGE'],
    ['solace',    400, 1164, 1, 4, 'SOLACE'],
    ['classic',   400, 1164, 1, 4, 'CLASSIC'],
    ['with_love', 400, 1164, 1, 4, 'WITH LOVE'],
    ['holidays',  400, 1164, 1, 4, 'HOLIDAYS'],
];

$themedIds = ['ar', 'hearts', 'dog', 'cat', 'bunny', 'fox', 'cool', 'vintage', 'solace', 'classic', 'with_love', 'holidays'];

foreach ($defs as [$id, $W, $H, $cols, $rows, $label]) {
    $theme = in_array($id, $themedIds) ? $id : null;
    $polaroid = $id === 'polaroid';
    $n = $cols * $rows;

    $im = imagecreatetruecolor($W, $H);
    imagefill($im, 0, 0, col($im, '#ffffff'));

    $margin = $W >= 600 ? 30 : 24;
    $gap = 14;
    // latar foto (gradasi cerah, warna lembut beda tiap orang)
    $pals = [['#ffe4c9', '#f6a8a0'], ['#cfe4ff', '#8da8f0'], ['#d5f8e3', '#7fcfa4'], ['#f2d9f7', '#c79af0']];

    if ($polaroid) {
        $pw = $W - $margin * 2 - 30; $ph = $pw;
        $px = (int)(($W - $pw) / 2); $py = $margin;
        vgrad($im, $px, $py, $pw, $ph, $pals[0][0], $pals[0][1]);
        drawPortrait($im, $px, $py, $pw, $ph, $theme, 0);
        imagerectangle($im, $px, $py, $px + $pw, $py + $ph, col($im, '#ffffff'));
    } else {
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
            // kartu foto dengan sudut membulat-ish (imple internal gambar)
            imagerectangle($im, $x, $y, $x + $pw, $y + $ph, col($im, '#ffffff', 18));
            imagerectangle($im, $x + 2, $y + 2, $x + $pw - 2, $y + $ph - 2, col($im, '#000000', 8));
        }
    }

    // dekorasi sisi kiri/kanan sesuai tema (ikon emoji via teks tidak dipakai, gambar bentuk)
    $deco = [
        'hearts'    => ['#e0245e', 'heart'],
        'with_love' => ['#e0245e', 'heart'],
        'dog'       => ['#8B5A2B', 'dot'],
        'cat'       => ['#3b2a1e', 'dot'],
        'bunny'     => ['#f284a8', 'dot'],
        'fox'       => ['#e8833a', 'dot'],
        'cool'      => ['#14141c', 'dot'],
        'solace'    => ['#a5b4fc', 'dot'],
        'holidays'  => ['#facc15', 'star'],
        'classic'   => ['#475569', 'diamond'],
        'vintage'   => ['#8b6b3a', 'diamond'],
    ];
    if (isset($deco[$id])) {
        [$dhex, $shape] = $deco[$id];
        $dcol = col($im, $dhex);
        for ($i = 0; $i < 8; $i++) {
            $dy = 50 + $i * (($H - 100) / 8);
            if ($shape === 'heart') { drawHeart($im, 13, (int)$dy, 7, $dcol); drawHeart($im, $W - 13, (int)$dy, 7, $dcol); }
            elseif ($shape === 'star') { drawStar($im, 13, (int)$dy, 7, $dcol); drawStar($im, $W - 13, (int)$dy, 7, $dcol); }
            elseif ($shape === 'diamond') {
                foreach ([13, $W - 13] as $dx) {
                    imagefilledpolygon($im, [$dx, (int)$dy - 6, $dx + 5, (int)$dy, $dx, (int)$dy + 6, $dx - 5, (int)$dy], 4, $dcol);
                }
            }
            else { imagefilledellipse($im, 13, (int)$dy, 9, 9, $dcol); imagefilledellipse($im, $W - 13, (int)$dy, 9, 9, $dcol); }
        }
    }

    // border luar lembut
    imagerectangle($im, 6, 6, $W - 7, $H - 7, col($im, '#e2e8f0'));
    imagerectangle($im, 3, 3, $W - 4, $H - 4, col($im, '#f1f5f9'));

    // footer brand + tanggal
    $sizeLabel = $cols > 1 ? "Size 6 x 4  (" . $n . " Pose)" : "Size 6 x 2  (" . $n . " Pose)";
    if ($polaroid) $sizeLabel = '';
    $brandU = strtoupper($label);
    $fg = imagecolorallocate($im, 30, 41, 59);
    $fgSoft = imagecolorallocate($im, 100, 116, 139);
    $brandCol = col($im, '#e0245e');
    $footY = $H - ($W < 600 ? 96 : 56);
    imagestring($im, 3, (int)($W/2) - (int)(strlen($brandU) * 4.2), $footY - 14, $brandU, $brandCol);
    imagestring($im, 2, (int)($W/2) - (int)(strlen($sizeLabel) * 3.4), $footY + 8, $sizeLabel, $fgSoft);
    imagestring($im, 1, (int)($W/2) - 18, $footY + 30, date('d M Y'), $fgSoft);

    imagepng($im, $outDir . '/' . $id . '.png');
    imagedestroy($im);
    echo "Generated: " . $id . ".png ({$W}x{$H})\n";
}

echo "Selesai. " . count($defs) . " preview dibuat di public/layout-previews/\n";