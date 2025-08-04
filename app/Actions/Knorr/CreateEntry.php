<?php

namespace App\Actions\Knorr;

use App\Models\Entry;
use Illuminate\Support\Facades\DB;

final class CreateEntry
{
    public function handle(array $data):Entry
    {
        $entry = null;
        DB::transaction(function () use (&$entry, $data) {
            $sqids = app(\Sqids\Sqids::class);
            [$batchNumber, $serialNumber] = $sqids->decode($data['code']);

            $entry = Entry::create([
                ...$data,
                'batch_number' => $batchNumber,
                'serial_number' => $serialNumber
            ]);
        });
        

        return $entry;
    }
}