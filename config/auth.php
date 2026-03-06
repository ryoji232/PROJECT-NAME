<?php
return [
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        'librarian' => [
            'driver' => 'session',
            'provider' => 'librarians',
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],
        'librarians' => [
            'driver' => 'eloquent',
            'model' => App\Models\Librarian::class,
        ],
    ],
];