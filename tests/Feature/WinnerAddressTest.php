<?php

declare(strict_types=1);

use App\Models\Entry;
use App\Models\Reward;
use Inertia\Testing\AssertableInertia as Assert;

it('shows the address form for a winning result with a valid token', function (): void {
    $entry = winnerEntry();

    $this->get(route('result', [
        'code' => $entry->code,
        'token' => $entry->addressToken(),
    ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Landing')
            ->where('showAddressForm', true)
            ->where('addressProvided', false)
            ->where('addressToken', $entry->addressToken())
            ->missing('entry.full_name')
            ->missing('entry.zipcode')
            ->missing('entry.house_number'));
});

it('hides the address form without a valid token', function (mixed $token): void {
    $entry = winnerEntry();

    $this->get(route('result', [
        'code' => $entry->code,
        'token' => $token,
    ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('showAddressForm', false)
            ->where('addressProvided', false)
            ->where('addressToken', null));
})->with([
    'missing' => null,
    'wrong' => 'not-the-token',
]);

it('shows the shipping confirmation once the address is saved', function (): void {
    $entry = winnerEntry([
        'full_name' => 'Michel van Dam',
        'zipcode' => '1234 AB',
        'house_number' => '12a',
    ]);

    $this->get(route('result', [
        'code' => $entry->code,
        'token' => $entry->addressToken(),
    ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('showAddressForm', false)
            ->where('addressProvided', true)
            ->where('addressToken', null)
            ->missing('entry.full_name')
            ->missing('entry.zipcode')
            ->missing('entry.house_number'));
});

it('stores a winner address once', function (): void {
    $entry = winnerEntry();

    $this->post(route('result.address', $entry->code), [
        'token' => $entry->addressToken(),
        'full_name' => ' Michel van Dam ',
        'zipcode' => '1234ab',
        'house_number' => '12a',
    ])->assertRedirect(route('result', [
        'code' => $entry->code,
        'token' => $entry->addressToken(),
    ]));

    $this->assertDatabaseHas('entries', [
        'id' => $entry->id,
        'full_name' => 'Michel van Dam',
        'zipcode' => '1234 AB',
        'house_number' => '12a',
    ]);

    $this->post(route('result.address', $entry->code), [
        'token' => $entry->addressToken(),
        'full_name' => 'Iemand Anders',
        'zipcode' => '5678 CD',
        'house_number' => '99',
    ])->assertRedirect(route('result', [
        'code' => $entry->code,
        'token' => $entry->addressToken(),
    ]));

    $this->assertDatabaseHas('entries', [
        'id' => $entry->id,
        'full_name' => 'Michel van Dam',
        'zipcode' => '1234 AB',
        'house_number' => '12a',
    ]);
});

it('rejects an address for an entry without a reward', function (): void {
    $entry = Entry::factory()->create([
        'code' => campaignCode(),
        'batch_number' => 0,
        'serial_number' => 1,
    ]);

    $this->post(route('result.address', $entry->code), [
        'token' => $entry->addressToken(),
        'full_name' => 'Michel van Dam',
        'zipcode' => '1234 AB',
        'house_number' => '12',
    ])->assertNotFound();

    $this->assertDatabaseHas('entries', [
        'id' => $entry->id,
        'full_name' => null,
    ]);
});

it('rejects an address with the wrong token', function (): void {
    $entry = winnerEntry();

    $this->post(route('result.address', $entry->code), [
        'token' => 'not-the-token',
        'full_name' => 'Michel van Dam',
        'zipcode' => '1234 AB',
        'house_number' => '12',
    ])->assertForbidden();

    $this->assertDatabaseHas('entries', [
        'id' => $entry->id,
        'full_name' => null,
    ]);
});

it('rejects an address for an unknown result code', function (): void {
    $this->post(route('result.address', campaignCode(1, 2)), [
        'token' => 'not-the-token',
        'full_name' => 'Michel van Dam',
        'zipcode' => '1234 AB',
        'house_number' => '12',
    ])->assertNotFound();
});

it('requires the address fields', function (): void {
    $entry = winnerEntry();

    $this->from(route('result', [
        'code' => $entry->code,
        'token' => $entry->addressToken(),
    ]))
        ->post(route('result.address', $entry->code), [])
        ->assertRedirect(route('result', [
            'code' => $entry->code,
            'token' => $entry->addressToken(),
        ]))
        ->assertSessionHasErrors([
            'token' => 'Token is verplicht.',
            'full_name' => 'Volledige naam is verplicht.',
            'zipcode' => 'Postcode is verplicht.',
            'house_number' => 'Huisnummer is verplicht.',
        ]);
});

it('rejects an invalid postcode', function (): void {
    $entry = winnerEntry();

    $this->from(route('result', [
        'code' => $entry->code,
        'token' => $entry->addressToken(),
    ]))
        ->post(route('result.address', $entry->code), [
            'token' => $entry->addressToken(),
            'full_name' => 'Michel van Dam',
            'zipcode' => '0123 AB',
            'house_number' => '12',
        ])
        ->assertRedirect(route('result', [
            'code' => $entry->code,
            'token' => $entry->addressToken(),
        ]))
        ->assertSessionHasErrors([
            'zipcode' => 'Het formaat van postcode is ongeldig.',
        ]);

    $this->assertDatabaseHas('entries', [
        'id' => $entry->id,
        'full_name' => null,
    ]);
});

it('rejects an address field that is too long', function (string $field, string $value, string $message): void {
    $entry = winnerEntry();

    $payload = [
        'token' => $entry->addressToken(),
        'full_name' => 'Michel van Dam',
        'zipcode' => '1234 AB',
        'house_number' => '12',
        $field => $value,
    ];

    $this->from(route('result', [
        'code' => $entry->code,
        'token' => $entry->addressToken(),
    ]))
        ->post(route('result.address', $entry->code), $payload)
        ->assertRedirect(route('result', [
            'code' => $entry->code,
            'token' => $entry->addressToken(),
        ]))
        ->assertSessionHasErrors([
            $field => $message,
        ]);
})->with([
    'full name' => ['full_name', str_repeat('a', 256), 'Volledige naam mag niet uit meer dan 255 tekens bestaan.'],
    'house number' => ['house_number', str_repeat('1', 21), 'Huisnummer mag niet uit meer dan 20 tekens bestaan.'],
]);

/**
 * @param  array<string, mixed>  $attributes
 */
function winnerEntry(array $attributes = []): Entry
{
    $entry = Entry::factory()->create($attributes);

    Reward::factory()->create([
        'entry_id' => $entry->id,
    ]);

    return Entry::query()->findOrFail($entry->id);
}
