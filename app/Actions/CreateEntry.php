<?php

declare(strict_types=1);

namespace App\Actions;

use App\Exceptions\DailySubmissionLimitReached;
use App\Mail\RewardConfirmationEmail;
use App\Models\Entry;
use App\Models\Reward;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Sqids\Sqids;

final readonly class CreateEntry
{
    public function __construct(
        private Sqids $sqids,
        private AssignReward $assignReward,
        private CreateReward $createReward
    ) {}

    /**
     * @param  array{code: string, name: string, email: string, soup: string}  $data
     */
    public function handle(array $data): Entry
    {
        $email = mb_strtolower($data['email']);

        return Cache::lock('entry-daily:'.hash('xxh128', $email), 10)->block(5, fn (): Entry => DB::transaction(function () use ($data, $email): Entry {
            $this->ensureWithinDailyLimit($email);

            $decoded = $this->sqids->decode($data['code']);
            [$batchNumber, $serialNumber] = $decoded;

            if (env('APP_ENV') !== 'production') {
                if (preg_match('/\+(sokken|kachel|kruik|kussen|deken)@/', $data['email'], $matches)) {
                    $rewardSuffix = $matches[1];
                    $this->createReward->handle($rewardSuffix);
                }
            }

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
        }));
    }

    private function ensureWithinDailyLimit(string $email): void
    {
        $startOfDay = today();

        $submissionsToday = Entry::query()
            ->whereRaw('lower(email) = ?', [$email])
            ->where('created_at', '>=', $startOfDay)
            ->where('created_at', '<', $startOfDay->copy()->addDay())
            ->count();

        throw_if($submissionsToday >= DailySubmissionLimitReached::LIMIT, DailySubmissionLimitReached::class);
    }
}
