<?php

declare(strict_types=1);

use App\Views\Engines\MJMLEngine;
use Illuminate\Support\Facades\File;

it('compiles a blade template and converts it', function (): void {
    $path = storage_path('framework/testing-email.mjml.blade.php');
    File::put($path, '<p>{{ $name }}</p>');

    $engine = new MJMLEngine(fn (string $compiled): string => 'converted:'.$compiled);

    expect($engine->get($path, ['name' => 'Michel']))->toBe('converted:<p>Michel</p>');

    File::delete($path);
});
