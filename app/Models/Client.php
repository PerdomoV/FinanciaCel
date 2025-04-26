<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'name',
        'email',
        'credit_score',
        'cc',
        'phone_number',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'credit_score' => 'float',
    ];

    /**
     * Get the credit applications for the client.
     */
    public function creditApplications(): HasMany
    {
        return $this->hasMany(CreditApplication::class);
    }
} 