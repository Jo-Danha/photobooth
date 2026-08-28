<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Daftar Paket / Layout Photobooth
    |--------------------------------------------------------------------------
    | Dipakai di halaman booth (index) dan admin (pengaturan harga).
    | Harga default bisa di-override dari Panel Admin via kolom layout_prices.
    */
    'packages' => [
        [
            'id'          => 'strip_4',
            'name'        => 'Layout B - Strip 4 Pose',
            'description' => 'Strip klasik 4 foto vertikal (6x2)',
            'shots'       => 4,
            'duration'    => 5,
            'price'       => 15000,
            'popular'     => true,
        ],
        [
            'id'          => 'strip_3',
            'name'        => 'Layout A - Strip 3 Pose',
            'description' => 'Strip 3 foto vertikal proporsional (6x2)',
            'shots'       => 3,
            'duration'    => 5,
            'price'       => 12000,
            'popular'     => false,
        ],
        [
            'id'          => 'strip_2',
            'name'        => 'Layout C - Strip 2 Pose',
            'description' => 'Strip 2 foto ekspresif ukuran besar (6x2)',
            'shots'       => 2,
            'duration'    => 4,
            'price'       => 10000,
            'popular'     => false,
        ],
        [
            'id'          => 'strip_6',
            'name'        => 'Layout D - Strip 6 Pose',
            'description' => 'Strip lebar 6 foto (6x4) 2 kolom',
            'shots'       => 6,
            'duration'    => 9,
            'price'       => 25000,
            'popular'     => false,
        ],
        [
            'id'          => 'strip_e',
            'name'        => 'Layout E - Strip 4 Pose',
            'description' => 'Strip lebar 4 foto (6x4) 2 kolom',
            'shots'       => 4,
            'duration'    => 7,
            'price'       => 20000,
            'popular'     => false,
        ],
        [
            'id'          => 'grid_4',
            'name'        => 'Grid 2x2 (4 Foto)',
            'description' => 'Layout kotak 4 foto modern untuk Instagram & cetak',
            'shots'       => 4,
            'duration'    => 7,
            'price'       => 20000,
            'popular'     => false,
        ],
        [
            'id'          => 'polaroid',
            'name'        => 'Polaroid Retro',
            'description' => 'Format polaroid retro dengan ruang catatan di bawah',
            'shots'       => 1,
            'duration'    => 4,
            'price'       => 8000,
            'popular'     => false,
        ],
        [
            'id'          => 'hearts',
            'name'        => 'Hearts Filter Layout',
            'description' => 'Strip 4 foto dengan hiasan hati (6x2)',
            'shots'       => 4,
            'duration'    => 5,
            'price'       => 15000,
            'popular'     => false,
        ],
        [
            'id'          => 'dog',
            'name'        => 'Dog Filter Layout',
            'description' => 'Strip 4 foto dengan hiasan telapak kaki anjing (6x2)',
            'shots'       => 4,
            'duration'    => 5,
            'price'       => 15000,
            'popular'     => false,
        ],
        [
            'id'          => 'vintage',
            'name'        => 'Vintage Layout',
            'description' => 'Strip 4 foto gaya vintage film jadul (6x2)',
            'shots'       => 4,
            'duration'    => 5,
            'price'       => 15000,
            'popular'     => false,
        ],
        [
            'id'          => 'solace',
            'name'        => 'Solace Layout',
            'description' => 'Strip 4 foto nuansa lembut pastel (6x2)',
            'shots'       => 4,
            'duration'    => 5,
            'price'       => 15000,
            'popular'     => false,
        ],
        [
            'id'          => 'classic',
            'name'        => 'Classic Layout',
            'description' => 'Strip 4 foto elegansi klasik (6x2)',
            'shots'       => 4,
            'duration'    => 5,
            'price'       => 15000,
            'popular'     => false,
        ],
        [
            'id'          => 'with_love',
            'name'        => 'With Love Layout',
            'description' => 'Strip 4 foto romantis "with love" (6x2)',
            'shots'       => 4,
            'duration'    => 5,
            'price'       => 15000,
            'popular'     => false,
        ],
        [
            'id'          => 'holidays',
            'name'        => 'Holidays Layout',
            'description' => 'Strip 4 foto tema liburan bintang salju (6x2)',
            'shots'       => 4,
            'duration'    => 5,
            'price'       => 15000,
            'popular'     => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Ukuran Canvas Hasil (pixel) per Layout
    |--------------------------------------------------------------------------
    | Frame PNG/upload akan di-stretch ke ukuran ini, jadi template harus
    | memiliki rasio aspek yang SAMA dengan tujuannya agar tidak melar.
    | Disarankan membuat template 2x-3x dari angka ini (crisp untuk cetak).
    */
    'frame_sizes' => [
        'strip_4'   => ['w' => 400, 'h' => 1164, 'label' => 'Strip 1 kolom (4 foto)'],
        'strip_3'   => ['w' => 400, 'h' => 906,  'label' => 'Strip 1 kolom (3 foto)'],
        'strip_2'   => ['w' => 400, 'h' => 648,  'label' => 'Strip 1 kolom (2 foto)'],
        'hearts'    => ['w' => 400, 'h' => 1164, 'label' => 'Strip 1 kolom (4 foto)'],
        'dog'       => ['w' => 400, 'h' => 1164, 'label' => 'Strip 1 kolom (4 foto)'],
        'vintage'   => ['w' => 400, 'h' => 1164, 'label' => 'Strip 1 kolom (4 foto)'],
        'solace'    => ['w' => 400, 'h' => 1164, 'label' => 'Strip 1 kolom (4 foto)'],
        'classic'   => ['w' => 400, 'h' => 1164, 'label' => 'Strip 1 kolom (4 foto)'],
        'with_love' => ['w' => 400, 'h' => 1164, 'label' => 'Strip 1 kolom (4 foto)'],
        'holidays'  => ['w' => 400, 'h' => 1164, 'label' => 'Strip 1 kolom (4 foto)'],
        'grid_4'    => ['w' => 678, 'h' => 618,  'label' => 'Grid 2 kolom (4 foto)'],
        'strip_e'   => ['w' => 678, 'h' => 618,  'label' => 'Grid 2 kolom (4 foto)'],
        'strip_6'   => ['w' => 678, 'h' => 861,  'label' => 'Grid 2 kolom (6 foto)'],
        'polaroid'  => ['w' => 420, 'h' => 500,  'label' => 'Polaroid (1 foto)'],
    ],

];
