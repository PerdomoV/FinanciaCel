<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Installment extends Model
{
    protected $fillable = [
        'application_id',
        'quantity',
        'amount',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Get the credit application that owns the installment.
     */
    public function creditApplication(): BelongsTo
    {
        return $this->belongsTo(CreditApplication::class, 'application_id');
    }
} 