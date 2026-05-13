<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    protected $fillable = ['name', 'description', 'release_at', 'entry_id'];

    protected $visible = ['name', 'description'];

    public function entry()
    {
        return $this->belongsTo(Entry::class);
    }
}
