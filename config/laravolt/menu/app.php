<?php

return [
    'App' => [
        'order' => 1,
        'menu' => [
            'Home' => [
                'route' => 'home',
                'active' => 'home/*',
                'icon' => 'home',
            ],
            'Report' => [
                'url' => '/resource/report',
                'active' => '/resource/report/*',
                'icon' => 'chart-pie',
                'permissions' => ['laravolt::manage-system'],
            ],
        ],
    ],
];
