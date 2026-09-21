<?php

declare(strict_types=1);

use App\Rules\CampaignEmail;

it('returns strict dns rules by default', function (): void {
    expect(CampaignEmail::rules())->toBe(['required', 'email:rfc,dns,strict,filter,spoof', 'max:255']);
});

it('returns test-friendly rules when dns is disabled', function (): void {
    expect(CampaignEmail::rules(strictDns: false))->toBe(['required', 'email:rfc,strict,filter', 'max:255']);
});
