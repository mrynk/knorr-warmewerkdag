<?php

declare(strict_types=1);

it('runs the database seeder', function (): void {
    $this->seed();

    expect(true)->toBeTrue();
});
