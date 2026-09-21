<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Mail\RewardConfirmationEmail;
use App\Models\Entry;
use Illuminate\Contracts\Foundation\Application;

final readonly class RewardConfirmationPreviewController
{
    public function __construct(private Application $app) {}

    public function __invoke(string $code): RewardConfirmationEmail
    {
        abort_unless($this->app->isLocal(), 404);

        $entry = Entry::query()->where('code', $code)->firstOrFail();

        return new RewardConfirmationEmail($entry);
    }
}
