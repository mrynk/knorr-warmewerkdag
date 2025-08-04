<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Entry extends Model
{
    protected $fillable = ['batch_number', 'serial_number', 'code', 'name', 'email', 'soup', 'reward'];

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
}
