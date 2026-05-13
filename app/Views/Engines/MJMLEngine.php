<?php

namespace App\Views\Engines;

use Illuminate\Contracts\View\Engine;
use Illuminate\Support\Facades\Blade;
use Spatie\Mjml\Mjml;

class MJMLEngine implements Engine
{
    public function get($path, array $data = [])
    {
        // Compile la vue Blade en HTML
        $viewContent = file_get_contents($path);
        $compiledView = Blade::render($viewContent, $data);

        // Post-process avec MJML
        return Mjml::new()
            ->beautify(false)
            ->minify(true)
            ->keepComments(false)
            ->convert($compiledView, ...[])
            ->html();
    }
}
