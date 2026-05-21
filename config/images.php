<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Image compression presets
    |--------------------------------------------------------------------------
    |
    | Images are scaled down to fit within max_width x max_height (keeping
    | aspect ratio), then encoded as WebP when supported, otherwise JPEG.
    |
    */

    'presets' => [
        'hero' => [
            'max_width' => 1920,
            'max_height' => 1080,
            'quality' => 82,
        ],
        'content' => [
            'max_width' => 1600,
            'max_height' => 1600,
            'quality' => 82,
        ],
        'gallery' => [
            'max_width' => 1920,
            'max_height' => 1920,
            'quality' => 80,
        ],
        'offre' => [
            'max_width' => 1200,
            'max_height' => 1200,
            'quality' => 82,
        ],
        'logo' => [
            'max_width' => 512,
            'max_height' => 512,
            'quality' => 88,
        ],
        'avatar' => [
            'max_width' => 400,
            'max_height' => 400,
            'quality' => 82,
        ],
    ],

];
