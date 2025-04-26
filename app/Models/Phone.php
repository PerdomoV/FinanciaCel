<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Phone extends Model
{
    protected $fillable = [
        'model',
        'price',
        'stock',
    ];

    /**
     * Get the credit applications for this phone.
     */
    public function creditApplications(): HasMany
    {
        return $this->hasMany(CreditApplication::class);
    }
} 