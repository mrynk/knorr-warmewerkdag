<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Sqids\Sqids;

#[Description('Generate unique campaign codes for each configured batch.')]
#[Signature('app:generate-codes {--force} {--path=}')]
final class GenerateCodesCommand extends Command
{
    public function __construct(private readonly Sqids $sqids)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        /** @var array<int, array{name: string, amount: int}> $batches */
        $batches = config('unique_codes.batches');
        $appName = config()->string('app.name');
        $pathOption = $this->option('path');
        $outputPath = is_string($pathOption) && $pathOption !== '' ? $pathOption : storage_path('app');

        File::ensureDirectoryExists($outputPath);

        foreach ($batches as $batchNumber => $batch) {
            $fileName = "{$appName}-{$batchNumber}-{$batch['name']}-{$batch['amount']}.csv";
            $filePath = $outputPath.DIRECTORY_SEPARATOR.$fileName;

            if (File::exists($filePath) && ! $this->option('force')) {
                $this->error("File already exists: {$fileName}");
                $this->error('Please remove or rename the existing file before generating new codes.');

                return self::FAILURE;
            }

            $lines = ['Code'];

            for ($i = 1; $i <= $batch['amount']; $i++) {
                $lines[] = $this->sqids->encode([$batchNumber, $i]);
            }

            File::put($filePath, implode("\n", $lines)."\n");
        }

        return self::SUCCESS;
    }
}
