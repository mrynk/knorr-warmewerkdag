<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Entry extends Model
{
    protected $fillable = ['batch_number', 'serial_number', 'code', 'name', 'email', 'soup', 'reward'];

    protected $visible = ['code', 'name', 'soup', 'reward', 'masked_email'];

    protected $appends = ['masked_email'];

    protected $with = ['reward'];

    public static function boot()
    {
        parent::boot();
        self::created(function ($entry) {
            $reward = Reward::where('release_at', '<=', now())->where('entry_id', null)->orderBy('release_at', 'asc')->first();
            if ($reward) {
                $reward->entry()->associate($entry);
                $reward->save();
            }
        });
    }

    public function reward()
    {
        return $this->hasOne(Reward::class);
    }

    protected function maskedEmail(): Attribute
    {
        [$username, $domain] = explode('@', $this->email);
        $level = 1;
        if (strlen($username) > 8) {
            $level = 2;
        }

        return new Attribute(
            get: fn () => substr($username, 0, $level).'***'.substr($username, -$level).'@'.$domain,
        );
    }
}
