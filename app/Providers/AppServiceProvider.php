<?php

namespace App\Providers;

use App\Views\Engines\MJMLEngine;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\Sqids\Sqids::class, function ($app) {
            return new \Sqids\Sqids(
                alphabet: $this->shuffleString(config('unique_codes.alphabet'), config('unique_codes.seed')),
                minLength: config('unique_codes.min_length'),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('redeem', function (Request $request) {
            return Limit::perMinute(10);
        });
        View::getEngineResolver()->register('mjml', function () {
            return new MJMLEngine;
        });

        View::addExtension('mjml.blade.php', 'mjml');
    }

    /**
     * Shuffle a string based on a custom seed.
     */
    private function shuffleString(string $string, int $seed): string
    {
        // Convert string to array of characters
        $chars = str_split($string);

        // Set the random seed for consistent shuffling
        mt_srand($seed);

        // Shuffle the array using Fisher-Yates algorithm with seeded random
        $length = count($chars);
        for ($i = $length - 1; $i > 0; $i--) {
            $j = mt_rand(0, $i);
            // Swap elements
            $temp = $chars[$i];
            $chars[$i] = $chars[$j];
            $chars[$j] = $temp;
        }

        // Convert back to string
        return implode('', $chars);
    }
}
