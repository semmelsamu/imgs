<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Input directory
    |--------------------------------------------------------------------------
    | The fully qualified path to the directory that will be scanned for
    | source images. Defaults to the public/images folder.
    */
    'input_dir' => public_path('images'),

    /*
    |--------------------------------------------------------------------------
    | Output directory
    |--------------------------------------------------------------------------
    | The fully qualified path where optimized images will be written.
    | Must be publicly accessible (i.e. inside public/).
    */
    'output_dir' => public_path('imgs'),

    /*
    |--------------------------------------------------------------------------
    | Output sizes
    |--------------------------------------------------------------------------
    | The pixel widths to generate. Images narrower than a given size will
    | skip that size - except for the first entry, which is always produced
    | as a guaranteed smallest fallback.
    */
    'sizes' => [400, 800, 1200, 1920],

    /*
    |--------------------------------------------------------------------------
    | Output format
    |--------------------------------------------------------------------------
    | The format Intervention Image will encode to. Common values:
    | 'webp', 'jpg', 'avif', 'png'
    */
    'format' => 'webp',

    /*
    |--------------------------------------------------------------------------
    | Output quality
    |--------------------------------------------------------------------------
    | Compression quality from 1 (worst) to 100 (best).
    */
    'quality' => 80,

];
