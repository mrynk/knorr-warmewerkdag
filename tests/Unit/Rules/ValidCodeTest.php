<?php

declare(strict_types=1);

use App\Rules\ValidCode;
use Illuminate\Support\Facades\Validator;
use Sqids\Sqids;

it('accepts a valid campaign code', function (): void {
    $validator = Validator::make(
        ['code' => campaignCode()],
        ['code' => [resolve(ValidCode::class)]],
    );

    expect($validator->passes())->toBeTrue();
});

it('rejects a non-string value', function (): void {
    $validator = Validator::make(
        ['code' => 12345678],
        ['code' => [resolve(ValidCode::class)]],
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('code'))->toContain('(001)');
});

it('rejects a code that cannot be decoded', function (): void {
    $validator = Validator::make(
        ['code' => 'INVALID1'],
        ['code' => [resolve(ValidCode::class)]],
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('code'))->toContain('(001)');
});

it('rejects a code that is not the canonical encoding', function (): void {
    $sqids = Mockery::mock(Sqids::class);
    $sqids->shouldReceive('decode')->andReturn([0, 1]);
    $sqids->shouldReceive('encode')->andReturn('OTHERCOD');

    $validator = Validator::make(
        ['code' => 'FAKECODE'],
        ['code' => [new ValidCode($sqids)]],
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('code'))->toContain('(002)');
});

it('rejects an unknown batch', function (): void {
    $validator = Validator::make(
        ['code' => campaignCode(99, 1)],
        ['code' => [resolve(ValidCode::class)]],
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('code'))->toContain('(003)');
});

it('rejects a serial that exceeds the batch amount', function (): void {
    $validator = Validator::make(
        ['code' => campaignCode(0, 999999)],
        ['code' => [resolve(ValidCode::class)]],
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('code'))->toContain('(004)');
});
