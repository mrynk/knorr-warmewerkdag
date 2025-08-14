<?php

namespace App\Actions\Knorr;

use App\Mail\RewardConfirmationEmail;
use App\Models\Entry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

final class CreateEntry
{
    public function __construct(
        private readonly CreateReward $createReward
    ) {}

    public function handle(array $data): Entry
    {
        $entry = null;
        DB::transaction(function () use (&$entry, $data) {
            $sqids = app(\Sqids\Sqids::class);
            [$batchNumber, $serialNumber] = $sqids->decode($data['code']);

            if (env('APP_ENV') !== 'production') {
                if (preg_match('/\+(sokken|kachel|kruik|kussen|deken)@/', $data['email'], $matches)) {
                    $rewardSuffix = $matches[1];
                    $this->createReward->handle($rewardSuffix);
                }
            }

            $entry = Entry::create([
                ...$data,
                'batch_number' => $batchNumber,
                'serial_number' => $serialNumber,
            ]);

            if ($entry->reward) {
                Mail::to($entry->email)->send(new RewardConfirmationEmail($entry));
            }
        });

        return $entry;
    }
}
