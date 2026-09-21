<?php

declare(strict_types=1);

namespace App\Actions;

use App\Mail\RewardConfirmationEmail;
use App\Models\Entry;
use App\Models\Reward;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Sqids\Sqids;

final readonly class CreateEntry
{
    public function __construct(
        private Sqids $sqids,
        private AssignReward $assignReward,
    ) {}

    /**
     * @param  array{code: string, name: string, email: string, soup: string}  $data
     */
    public function handle(array $data): Entry
    {
        return DB::transaction(function () use ($data): Entry {
            $decoded = $this->sqids->decode($data['code']);
            [$batchNumber, $serialNumber] = $decoded;

            $entry = Entry::query()->create([
                ...$data,
                'batch_number' => $batchNumber,
                'serial_number' => $serialNumber,
            ]);

            $reward = $this->assignReward->handle($entry);

            if ($reward instanceof Reward) {
                Mail::to($entry->email)->queue(new RewardConfirmationEmail($entry->load('reward')));
            }

            return $entry->load('reward');
        });
    }
}
