<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SecondLevel extends Model
{
    public function thirdLevelModels(): HasMany
    {
        return $this->hasMany(ThirdLevel::class);
    }
}
