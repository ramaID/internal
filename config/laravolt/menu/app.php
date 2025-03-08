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
            'Project' => [
                'url' => '/resource/project',
                'active' => '/resource/project/*',
                'icon' => 'folder',
            ],
        ],
    ],
];
