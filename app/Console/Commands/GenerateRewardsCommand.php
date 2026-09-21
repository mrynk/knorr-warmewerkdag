<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\CreateReward;
use App\Models\Reward;
use Carbon\CarbonImmutable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

#[Description('Seed the timed reward pool and write a timesweep CSV.')]
#[Signature('app:generate-rewards {--path=}')]
final class GenerateRewardsCommand extends Command
{
    public function __construct(private readonly CreateReward $createReward)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $pathOption = $this->option('path');
        $csvPath = is_string($pathOption) && $pathOption !== '' ? $pathOption : resource_path('timesweep.csv');

        if (File::exists($csvPath)) {
            $this->info('Timesweep CSV file already exists...');

            return self::SUCCESS;
        }

        $this->info('Creating rewards...');

        /** @var array<string, array{name: string, title: string, description: string, amount: int}> $rewards */
        $rewards = json_decode(File::get(base_path('resources/rewards.json')), true, flags: JSON_THROW_ON_ERROR);
        $startDate = now()->setDateTime(2025, 11, 3, 0, 0, 0);
        $endDate = now()->setDateTime(2025, 12, 1, 0, 0, 0);

        foreach ($rewards as $reward => $data) {
            for ($i = 0; $i < $data['amount']; $i++) {
                $releaseAt = CarbonImmutable::createFromTimestamp(random_int($startDate->getTimestamp(), $endDate->getTimestamp()));
                $this->createReward->handle($reward, $releaseAt);
            }
        }

        $rewardRecords = Reward::query()->oldest('release_at')->get();
        $lines = [
            $this->csvLine(['id', 'name', 'description', 'release_at', 'entry_id', 'created_at', 'updated_at']),
        ];

        foreach ($rewardRecords as $rewardRecord) {
            $lines[] = $this->csvLine([
                $rewardRecord->id,
                $rewardRecord->name,
                $rewardRecord->description,
                $rewardRecord->release_at->toDateTimeString(),
                $rewardRecord->entry_id,
                $rewardRecord->created_at->toDateTimeString(),
                $rewardRecord->updated_at->toDateTimeString(),
            ]);
        }

        File::ensureDirectoryExists(dirname($csvPath));
        File::put($csvPath, implode("\n", $lines)."\n");

        return self::SUCCESS;
    }

    /**
     * @param  list<int|float|string|null>  $fields
     */
    private function csvLine(array $fields): string
    {
        return implode(',', array_map(
            fn (int|float|string|null $field): string => '"'.str_replace('"', '""', (string) $field).'"',
            $fields,
        ));
    }
}
