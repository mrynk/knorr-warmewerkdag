<?php

declare(strict_types=1);

namespace App\Actions;

use Spatie\Mjml\Mjml;

final readonly class ConvertMjml
{
    public function handle(string $compiled): string
    {
        return Mjml::new()
            ->beautify(false)
            ->minify(true)
            ->keepComments(false)
            ->convert($compiled)
            ->html();
    }
}
