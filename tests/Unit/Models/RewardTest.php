<?php

declare(strict_types=1);

use App\Models\Entry;
use App\Models\Reward;

it('belongs to an entry', function (): void {
    $entry = Entry::factory()->create();
    $reward = Reward::factory()->create([
        'entry_id' => $entry->id,
    ]);

    expect($reward->entry?->is($entry))->toBeTrue();
});
