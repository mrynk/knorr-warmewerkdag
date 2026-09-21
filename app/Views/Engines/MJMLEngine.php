<?php

declare(strict_types=1);

namespace App\Views\Engines;

use Closure;
use Illuminate\Contracts\View\Engine;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;

final readonly class MJMLEngine implements Engine
{
    /**
     * @param  Closure(string): string  $converter
     */
    public function __construct(private Closure $converter) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function get($path, array $data = []): string
    {
        $compiledView = Blade::render(File::get($path), $data);

        return ($this->converter)($compiledView);
    }
}
