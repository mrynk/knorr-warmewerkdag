<?php

declare(strict_types=1);

it('has a landing page', function (): void {
    $page = visit('/');

    $page->assertSee('Vul je code in');
});
