<?php

return [
    'statuses' => ['Menunggu', 'Dikonfirmasi', 'Selesai', 'Dibatalkan'],

    'status_transitions' => [
        'Menunggu' => ['Dikonfirmasi', 'Dibatalkan'],
        'Dikonfirmasi' => ['Selesai', 'Dibatalkan'],
        'Selesai' => [],
        'Dibatalkan' => [],
    ],
];
