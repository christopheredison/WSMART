<?php

return [
    'url' => trim(env('WZONE_URL', 'https://new-portal.wika.co.id'), '/') . '/',
    'secret' => env('WZONE_SECRET', ''),
];