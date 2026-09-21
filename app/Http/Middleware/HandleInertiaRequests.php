<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Inertia\Middleware;

final class HandleInertiaRequests extends Middleware
{
    /**
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        /** @var array<string, array{id: string, name: string, advise: string, video: string}> $soups */
        $soups = json_decode(File::get(base_path('resources/soups.json')), true, flags: JSON_THROW_ON_ERROR);
        $soupIds = array_values($soups);

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'soups' => $soups,
            'soup_of_the_day' => $soupIds[now()->dayOfYear % count($soupIds)],
        ];
    }
}
