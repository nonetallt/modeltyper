<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FirstLevel extends Model
{
    protected $fillable = [
        'name',
        'second_level_models',
    ];

    public function secondLevelModels(): HasMany
    {
        return $this->hasMany(SecondLevel::class);
    }
}
