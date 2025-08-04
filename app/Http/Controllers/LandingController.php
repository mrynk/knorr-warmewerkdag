<?php

namespace App\Http\Controllers;

use App\Actions\Knorr\CreateEntry;
use App\Http\Requests\CreateEntryRequest;
use App\Models\Entry;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LandingController extends Controller
{
    public function index()
    {
        return Inertia::render('Landing');
    }

    public function redeem(CreateEntryRequest $request, CreateEntry $createEntry)
    {
        $validated = $request->validated();

        try {
            $entry = $createEntry->handle($validated);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->errorInfo[1] == 1062) { // MySQL duplicate entry error code
                \Log::error('Duplicate entry attempt', ['error' => $e->getMessage()]);
                return redirect()->route('home')->withErrors(['code' => 'Ongeldige actiecode (011)']);
            }
            \Log::error('Database error creating entry', ['error' => $e->getMessage()]);
            return redirect()->route('home')->withErrors(['code' => 'Ongeldige actiecode (012)']);
        } catch (\Exception $e) {
            \Log::error('Error creating entry', ['error' => $e->getMessage()]);
            return redirect()->route('home')->withErrors(['code' =>  'Ongeldige actiecode (010)']);
        }

        return redirect()->route('result', $entry->code);
    }

    public function result($code)
    {
        $sqids = app(\Sqids\Sqids::class);
        $decoded = $sqids->decode($code);

        if (empty($decoded) || count($decoded) !== 2) {
            return abort(404);
        }

        list($batchNumber, $serialNumber) = $decoded;

        $entry = Entry::where('code', $code)->where('batch_number', $batchNumber)->where('serial_number', $serialNumber)->firstOrFail();
        
        return Inertia::render('Landing', ['entry' => $entry]);
    }
}
