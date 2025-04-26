<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreditApplication extends Model
{
    protected $table = 'credit_applications';

    protected $fillable = [
        'client_id',
        'phone_id',
        'state',
        'amount',
        'term',
        'monthly_interest_rate',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'term' => 'integer',
        'monthly_interest_rate' => 'decimal:4',
    ];

    /**
     * Get the client that owns the credit application.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the phone associated with the credit application.
     */
    public function phone(): BelongsTo
    {
        return $this->belongsTo(Phone::class);
    }

    /**
     * Get the installments for this credit application.
     */
    public function installments(): HasMany
    {
        return $this->hasMany(Installment::class, 'application_id');
    }
} 