<?php

declare(strict_types=1);

namespace App\Providers;

use App\Actions\ConvertMjml;
use App\Views\Engines\MJMLEngine;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Sqids\Sqids;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(function (): Sqids {
            $alphabet = config()->string('unique_codes.alphabet');
            $seed = config()->integer('unique_codes.seed');
            $minLength = config()->integer('unique_codes.min_length');

            return new Sqids(
                alphabet: $this->shuffleString($alphabet, $seed),
                minLength: $minLength,
            );
        });
    }

    public function boot(ConvertMjml $convertMjml): void
    {
        RateLimiter::for('redeem', fn (): Limit => Limit::perMinute(10));

        View::getEngineResolver()->register('mjml', fn (): MJMLEngine => new MJMLEngine($convertMjml->handle(...)));
        View::addExtension('mjml.blade.php', 'mjml');
    }

    private function shuffleString(string $string, int $seed): string
    {
        $chars = mb_str_split($string);

        mt_srand($seed);

        $length = count($chars);
        for ($i = $length - 1; $i > 0; $i--) {
            $j = mt_rand(0, $i);
            $temp = $chars[$i];
            $chars[$i] = $chars[$j];
            $chars[$j] = $temp;
        }

        return implode('', $chars);
    }
}
