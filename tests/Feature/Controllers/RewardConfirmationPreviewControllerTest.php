<?php

declare(strict_types=1);

use App\Models\Entry;
use App\Models\Reward;

it('previews the confirmation email locally', function (): void {
    $this->app['env'] = 'local';

    $entry = Entry::factory()->create();
    Reward::factory()->create([
        'name' => 'sokken',
        'entry_id' => $entry->id,
    ]);

    $this->get(route('email.preview', $entry->code))->assertOk();
});

it('hides the confirmation email outside local', function (): void {
    $entry = Entry::factory()->create();

    $this->get(route('email.preview', $entry->code))->assertNotFound();
});
