<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\EntryFactory;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Visible;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property-read int $id
 * @property-read int $batch_number
 * @property-read int $serial_number
 * @property-read string $code
 * @property-read string $name
 * @property-read string $email
 * @property-read string $soup
 * @property string|null $full_name
 * @property string|null $zipcode
 * @property string|null $house_number
 * @property-read string $masked_email
 * @property-read Reward|null $reward
 */
#[Appends(['masked_email'])]
#[Visible(['code', 'name', 'soup', 'reward', 'masked_email'])]
final class Entry extends Model
{
    /** @use HasFactory<EntryFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $with = ['reward'];

    /**
     * @return HasOne<Reward, $this>
     */
    public function reward(): HasOne
    {
        return $this->hasOne(Reward::class);
    }

    public function addressToken(): string
    {
        return hash_hmac('sha256', $this->id.'|'.$this->code, config()->string('app.key'));
    }

    public function addressTokenMatches(mixed $token): bool
    {
        if (! is_string($token) || $token === '') {
            return false;
        }

        return hash_equals($this->addressToken(), $token);
    }

    public function shippingAddressIsEmpty(): bool
    {
        return $this->full_name === null
            && $this->zipcode === null
            && $this->house_number === null;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'batch_number' => 'integer',
            'serial_number' => 'integer',
            'code' => 'string',
            'name' => 'string',
            'email' => 'string',
            'soup' => 'string',
            'full_name' => 'string',
            'zipcode' => 'string',
            'house_number' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @return Attribute<non-falsy-string, never>
     */
    protected function maskedEmail(): Attribute
    {
        return Attribute::get(function (): string {
            [$username, $domain] = explode('@', $this->email);
            $level = mb_strlen($username) > 8 ? 2 : 1;

            return mb_substr($username, 0, $level).'***'.mb_substr($username, -$level).'@'.$domain;
        });
    }
}
