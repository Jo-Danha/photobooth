<?php
// Generate a sample "photo" used for booth layout previews (public/sample/sample.jpg)
if (!extension_loaded('gd')) { echo "GD not available\n"; exit(1); }

$dir = __DIR__ . '/../public/sample';
if (!is_dir($dir)) mkdir($dir, 0777, true);

$W = 1280; $H = 960;
$img = imagecreatetruecolor($W, $H);

// Studio backdrop gradient
function lerp($a, $b, $t) { return $a + ($b - $a) * $t; }
$top = [129, 140, 248];   // indigo
$bot = [244, 198, 240];   // pink
for ($y = 0; $y < $H; $y++) {
    $t = $y / $H;
    $r = lerp($top[0], $bot[0], $t);
    $g = lerp($top[1], $bot[1], $t);
    $b = lerp($top[2], $bot[2], $t);
    $col = imagecolorallocate($img, $r, $g, $b);
    imageline($img, 0, $y, $W, $y, $col);
}

// 4 "scene" quadrants with different subject colors so each grid cell differs
$scenes = [
    ['bg' => [255, 224, 178], 'subj' => [120, 80, 60]],   // warm
    ['bg' => [186, 230, 253], 'subj' => [67, 99, 140]],   // blue
    ['bg' => [187, 247, 208], 'subj' => [60, 120, 80]],    // green
    ['bg' => [254, 202, 202], 'subj' => [150, 60, 60]],    // red
];
$qw = $W / 2; $qh = $H / 2;
foreach ($scenes as $i => $s) {
    $qx = ($i % 2) * $qw;
    $qy = floor($i / 2) * $qh;
    $bg = imagecolorallocate($img, $s['bg'][0], $s['bg'][1], $s['bg'][2]);
    imagefilledrectangle($img, $qx, $qy, $qx + $qw, $qy + $qh, $bg);
    // simple person silhouette (head + shoulders)
    $cx = $qx + $qw / 2;
    $headR = $qh * 0.16;
    $subj = imagecolorallocate($img, $s['subj'][0], $s['subj'][1], $s['subj'][2]);
    imagefilledellipse($img, $cx, $qy + $qh * 0.42, $headR * 2, $headR * 2, $subj);
    imagefilledrectangle($img, $cx - $qw * 0.20, $qy + $qh * 0.60, $cx + $qw * 0.20, $qy + $qh * 0.92, $subj);
}

// Confetti / sparkles
$colors = [[250,204,21],[244,63,94],[34,197,94],[59,130,246],[168,85,247]];
for ($i = 0; $i < 80; $i++) {
    $c = $colors[array_rand($colors)];
    $col = imagecolorallocate($img, $c[0], $c[1], $c[2]);
    imagefilledrectangle($img, rand(0,$W), rand(0,$H), rand(0,$W)+6, rand(0,$H)+6, $col);
}

// Soft vignette
$vig = imagecreatetruecolor($W, $H);
imagefilledrectangle($vig, 0, 0, $W, $H, imagecolorallocatealpha($vig, 0,0,0,0));
$black = imagecolorallocatealpha($vig, 0,0,0, 60);
imagefilledrectangle($vig, 0, 0, $W, $H, $black);
// simpler: draw border darkening
imagerectangle($img, 4, 4, $W-4, $H-4, imagecolorallocate($img, 255,255,255));

imagejpeg($img, $dir . '/sample.jpg', 88);
imagedestroy($img);
echo "sample created: " . $dir . "/sample.jpg\n";
