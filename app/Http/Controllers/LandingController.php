<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateEntry;
use App\Http\Requests\CreateEntryRequest;
use App\Models\Entry;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Sqids\Sqids;
use Throwable;

final readonly class LandingController
{
    public function __construct(private Sqids $sqids) {}

    public function index(): Response
    {
        return Inertia::render('Landing');
    }

    public function store(CreateEntryRequest $request, CreateEntry $createEntry): RedirectResponse
    {
        try {
            $entry = $createEntry->handle($request->entryData());
        } catch (UniqueConstraintViolationException $exception) {
            Log::error('Duplicate entry attempt', ['error' => $exception->getMessage()]);

            return to_route('landing')->withErrors(['code' => 'Ongeldige actiecode (011)']);
        } catch (Throwable $exception) {
            Log::error('Error creating entry', ['error' => $exception->getMessage()]);

            return to_route('landing')->withErrors(['code' => 'Ongeldige actiecode (010)']);
        }

        return to_route('result', $entry->code);
    }

    public function show(string $code): Response
    {
        $decoded = $this->sqids->decode($code);

        abort_if(count($decoded) !== 2, 404);

        [$batchNumber, $serialNumber] = $decoded;

        $entry = Entry::query()
            ->where('code', $code)
            ->where('batch_number', $batchNumber)
            ->where('serial_number', $serialNumber)
            ->firstOrFail();

        return Inertia::render('Landing', ['entry' => $entry]);
    }
}
