<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Party extends Model
{
    use SoftDeletes;

    protected $table = 'parties';

    protected $fillable = [
        // System Identifiers
        'uuid',
        'party_code',

        // Basic Identity
        'type',
        'firstname',
        'middlename',
        'lastname',

        // Contact
        'email',
        'phone',
        'alternatemobile',
        'relative_mobile',
        'phone_number_2',
        'relative_phone',

        // Classification
        'source',
        'category',

        // Business
        'company_name',
        'gst_no',
        'pan_no',
        'tax_no',

        // Agriculture
        'land_area',
        'land_unit',
        'crops',
        'irrigation_type',

        // Financial
        'credit_limit',
        'credit_days',
        'outstanding_balance',
        'credit_valid_till',

        // KYC
        'aadhaar_last4',
        'kyc_completed',
        'kyc_verified_at',

        // Engagement
        'first_purchase_at',
        'last_purchase_at',
        'orders_count',

        // Status & Control
        'status',
        'is_active',
        'is_blacklisted',
        'internal_notes',
        'tags',

        // Accounting
        'account_type_id',

        // Audit
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'credit_limit'        => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
        'land_area'           => 'decimal:2',
        'credit_days'         => 'integer',
        'orders_count'        => 'integer',
        'is_active'           => 'boolean',
        'is_blacklisted'      => 'boolean',
        'kyc_completed'       => 'boolean',
        'crops'               => 'array',
        'tags'                => 'array',
        'credit_valid_till'   => 'date',
        'first_purchase_at'   => 'date',
        'last_purchase_at'    => 'date',
        'kyc_verified_at'     => 'datetime',
        'created_at'          => 'datetime',
        'updated_at'          => 'datetime',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'party_id');
    }

    public function addresses()
    {
        return $this->hasMany(PartyAddress::class, 'party_id');
    }

    // ─── Computed full name ───────────────────────────────────────────────────
    public function getNameAttribute(): string
    {
        return trim(collect([$this->firstname, $this->middlename, $this->lastname])
            ->filter()->implode(' '));
    }
}
