<?php

namespace App\Console\Commands;

use App\Actions\Knorr\CreateReward;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class GenerateRewards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-rewards';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function __construct(
        private readonly CreateReward $createReward
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (file_exists(resource_path('timesweep.csv'))) {
            $this->info('Timesweep CSV file already exists...');

            return;
        }
        $this->info('Creating rewards...');
        // create rewards for each reward in the rewards.json file and their amount. use a random Carbon date between 3-11-2025 and 15-12-2025
        $rewards = json_decode(file_get_contents(base_path('resources/rewards.json')), true);
        $start_Date = now()->setDateTime(2025, 11, 3, 0, 0, 0);
        $end_Date = now()->setDateTime(2025, 12, 1, 0, 0, 0);
        foreach ($rewards as $reward => $data) {
            for ($i = 0; $i < $data['amount']; $i++) {
                $release_at = rand($start_Date->timestamp, $end_Date->timestamp);
                $this->createReward->handle($reward, Carbon::createFromTimestamp($release_at));
            }
        }

        // Select all Reward records and write the records to a csv file called timesweep.csv

        // Get all Reward records
        $rewards = \App\Models\Reward::all();

        // Open (or create) the CSV file for writing
        $csvFile = fopen(resource_path('timesweep.csv'), 'w');

        if ($rewards->count() > 0) {
            // Write the header (column names)
            fputcsv($csvFile, array_keys($rewards->first()->getAttributes()));

            // Write each reward row
            foreach ($rewards as $rewardRecord) {
                fputcsv($csvFile, $rewardRecord->getAttributes());
            }
        }

        fclose($csvFile);
    }
}
