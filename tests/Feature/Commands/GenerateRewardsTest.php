<?php

declare(strict_types=1);

use App\Models\Reward;
use Illuminate\Support\Facades\File;

it('defaults to the resources timesweep path', function (): void {
    $path = resource_path('timesweep.csv');
    File::put($path, 'existing');

    $this->artisan('app:generate-rewards')
        ->expectsOutput('Timesweep CSV file already exists...')
        ->assertSuccessful();

    expect(Reward::query()->count())->toBe(0);

    File::delete($path);
});

it('returns early when the timesweep file already exists', function (): void {
    $path = storage_path('framework/testing-timesweep-exists.csv');
    File::put($path, 'existing');

    $this->artisan('app:generate-rewards', ['--path' => $path])
        ->expectsOutput('Timesweep CSV file already exists...')
        ->assertSuccessful();

    expect(Reward::query()->count())->toBe(0);

    File::delete($path);
});

it('creates rewards and writes a timesweep csv', function (): void {
    $path = storage_path('framework/testing-timesweep.csv');
    File::delete($path);

    $this->artisan('app:generate-rewards', ['--path' => $path])
        ->expectsOutput('Creating rewards...')
        ->assertSuccessful();

    expect(Reward::query()->count())->toBe(129)
        ->and(File::exists($path))->toBeTrue()
        ->and(File::get($path))->toContain('name');

    File::delete($path);
});
