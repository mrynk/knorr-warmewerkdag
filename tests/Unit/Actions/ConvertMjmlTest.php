<?php

declare(strict_types=1);

use App\Actions\ConvertMjml;

it('converts mjml to html', function (): void {
    $html = resolve(ConvertMjml::class)->handle(
        '<mjml><mj-body><mj-section><mj-column><mj-text>Hallo</mj-text></mj-column></mj-section></mj-body></mjml>',
    );

    expect($html)->toContain('Hallo');
});
