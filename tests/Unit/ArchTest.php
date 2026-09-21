<?php

declare(strict_types=1);

arch()->preset()->php();
arch()->preset()->strict()->ignoring([
    'App\Models',
]);
arch()->preset()->laravel();
arch()->preset()->security()->ignoring([
    'assert',
    'mt_rand',
]);

arch('controllers')
    ->expect('App\Http\Controllers')
    ->not->toBeUsed();

arch('actions')
    ->expect('App\Actions')
    ->toBeFinal()
    ->toHaveMethod('handle');
