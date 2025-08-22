<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-codes {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sqids = app(\Sqids\Sqids::class);
        foreach (config('unique_codes.batches') as $batchNumber => $batch) {
            $appName = config('app.name');
            $fileName = "{$appName}-{$batchNumber}-{$batch['name']}-{$batch['amount']}.csv";
            $filePath = storage_path("app/{$fileName}");

            // Check if file already exists and warn
            if (file_exists($filePath) && ! $this->option('force')) {
                $this->error("File already exists: {$fileName}");
                $this->error('Please remove or rename the existing file before generating new codes.');

                return 1;
            }

            // Create the file and add header
            file_put_contents($filePath, "Code\n");

            for ($i = 1; $i <= $batch['amount']; $i++) {
                $code = $sqids->encode([$batchNumber, $i]);

                // Append code to CSV file
                file_put_contents($filePath, "{$code}\n", FILE_APPEND | LOCK_EX);
            }
        }
    }
}
