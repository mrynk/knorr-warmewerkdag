<?php

declare(strict_types=1);

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Http\Request;

it('shares campaign soups', function (): void {
    $middleware = new HandleInertiaRequests;
    $shared = $middleware->share(Request::create('/'));

    expect($shared)->toHaveKeys(['name', 'soups', 'soup_of_the_day'])
        ->and($shared['soups'])->toHaveKey('turkse-linzen')
        ->and($shared['soup_of_the_day'])->toHaveKey('id');
});

it('uses the parent asset version', function (): void {
    $middleware = new HandleInertiaRequests;

    expect($middleware->version(Request::create('/')))->toBeString();
});
