<?php

declare(strict_types=1);

use App\Models\Entry;
use App\Models\Reward;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    config(['inertia.ssr.enabled' => false]);
});

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
            ->missing('entry.house_number')
            ->missing('entry.street')
            ->missing('entry.city'));
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
        'street' => 'Kalverstraat',
        'city' => 'Amsterdam',
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
            ->missing('entry.house_number')
            ->missing('entry.street')
            ->missing('entry.city'));
});

it('stores a winner address once', function (): void {
    $entry = winnerEntry();

    Http::fake([
        postcodeUrl('1234AB', '12', 'a') => postcodeResponse('a', 'Kalverstraat', 'Amsterdam'),
        postcodeUrl('5678CD', '99') => postcodeResponse('', 'Damrak', 'Amsterdam'),
    ]);

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
        'street' => 'Kalverstraat',
        'city' => 'Amsterdam',
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
        'street' => 'Kalverstraat',
        'city' => 'Amsterdam',
    ]);

    Http::assertSentCount(2);
});

it('rejects an address for an entry without a reward', function (): void {
    Http::fake([
        postcodeUrl('1234AB', '12') => postcodeResponse(''),
    ]);

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
    Http::fake([
        postcodeUrl('1234AB', '12') => postcodeResponse(''),
    ]);

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
    Http::fake([
        postcodeUrl('1234AB', '12') => postcodeResponse(''),
    ]);

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

it('rejects an unknown postcode and house number combination', function (): void {
    $entry = winnerEntry();

    Http::fake([
        postcodeUrl('1234AB', '12') => Http::response(['exception' => 'Combination does not exist.'], 404),
    ]);

    $this->from(route('result', [
        'code' => $entry->code,
        'token' => $entry->addressToken(),
    ]))
        ->post(route('result.address', $entry->code), [
            'token' => $entry->addressToken(),
            'full_name' => 'Michel van Dam',
            'zipcode' => '1234 AB',
            'house_number' => '12',
        ])
        ->assertRedirect(route('result', [
            'code' => $entry->code,
            'token' => $entry->addressToken(),
        ]))
        ->assertSessionHasErrors([
            'house_number' => 'Deze combinatie van postcode en huisnummer is ongeldig.',
        ]);

    $this->assertDatabaseHas('entries', [
        'id' => $entry->id,
        'full_name' => null,
        'street' => null,
        'city' => null,
    ]);
});

it('rejects a house number addition that does not exist', function (): void {
    $entry = winnerEntry();

    Http::fake([
        postcodeUrl('1234AB', '12', 'a') => Http::response([
            'street' => 'Kalverstraat',
            'city' => 'Amsterdam',
            'houseNumberAddition' => null,
            'houseNumberAdditions' => ['', 'b'],
        ]),
    ]);

    $this->from(route('result', [
        'code' => $entry->code,
        'token' => $entry->addressToken(),
    ]))
        ->post(route('result.address', $entry->code), [
            'token' => $entry->addressToken(),
            'full_name' => 'Michel van Dam',
            'zipcode' => '1234 AB',
            'house_number' => '12a',
        ])
        ->assertRedirect(route('result', [
            'code' => $entry->code,
            'token' => $entry->addressToken(),
        ]))
        ->assertSessionHasErrors([
            'house_number' => 'Deze combinatie van postcode en huisnummer is ongeldig.',
        ]);

    $this->assertDatabaseHas('entries', [
        'id' => $entry->id,
        'full_name' => null,
        'street' => null,
        'city' => null,
    ]);
});

it('rejects the address when postcode lookup is unavailable', function (): void {
    $entry = winnerEntry();

    Http::fake([
        postcodeUrl('1234AB', '12') => Http::response(['exception' => 'Server error.'], 500),
    ]);

    $this->from(route('result', [
        'code' => $entry->code,
        'token' => $entry->addressToken(),
    ]))
        ->post(route('result.address', $entry->code), [
            'token' => $entry->addressToken(),
            'full_name' => 'Michel van Dam',
            'zipcode' => '1234 AB',
            'house_number' => '12',
        ])
        ->assertRedirect(route('result', [
            'code' => $entry->code,
            'token' => $entry->addressToken(),
        ]))
        ->assertSessionHasErrors([
            'house_number' => 'Adrescontrole is tijdelijk niet beschikbaar. Probeer het later opnieuw.',
        ]);

    $this->assertDatabaseHas('entries', [
        'id' => $entry->id,
        'full_name' => null,
        'street' => null,
        'city' => null,
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

function postcodeUrl(string $postcode, string $houseNumber, string $addition = ''): string
{
    $url = 'https://api.postcode.eu/nl/v1/addresses/postcode/'.$postcode.'/'.$houseNumber;

    if ($addition !== '') {
        $url .= '/'.rawurlencode($addition);
    }

    return $url;
}

function postcodeResponse(string $addition, string $street = 'Kalverstraat', string $city = 'Amsterdam'): PromiseInterface
{
    return Http::response([
        'street' => $street,
        'city' => $city,
        'houseNumberAddition' => $addition,
    ]);
}

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
