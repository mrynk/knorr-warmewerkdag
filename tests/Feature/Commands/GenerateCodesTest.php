<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

it('generates campaign code csv files', function (): void {
    config()->set('unique_codes.batches', [
        ['name' => 'Test', 'amount' => 2],
    ]);

    $path = storage_path('framework/testing-codes');
    File::deleteDirectory($path);

    $this->artisan('app:generate-codes', ['--path' => $path])
        ->assertSuccessful();

    $files = File::files($path);

    expect($files)->toHaveCount(1)
        ->and(File::get($files[0]->getPathname()))->toContain("Code\n");

    File::deleteDirectory($path);
});

it('refuses to overwrite existing files without force', function (): void {
    config()->set('unique_codes.batches', [
        ['name' => 'Test', 'amount' => 1],
    ]);

    $path = storage_path('framework/testing-codes-existing');
    File::ensureDirectoryExists($path);
    $fileName = config('app.name').'-0-Test-1.csv';
    File::put($path.DIRECTORY_SEPARATOR.$fileName, "Code\nOLD\n");

    $this->artisan('app:generate-codes', ['--path' => $path])
        ->assertFailed();

    expect(File::get($path.DIRECTORY_SEPARATOR.$fileName))->toContain('OLD');

    $this->artisan('app:generate-codes', ['--path' => $path, '--force' => true])
        ->assertSuccessful();

    File::deleteDirectory($path);
});
