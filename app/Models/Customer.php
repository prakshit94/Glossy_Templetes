<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Customer extends Model
{
    use SoftDeletes, LogsActivity;

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
        'source'              => 'array',
        'irrigation_type'     => 'array',
        'credit_valid_till'   => 'date',
        'first_purchase_at'   => 'date',
        'last_purchase_at'    => 'date',
        'kyc_verified_at'     => 'datetime',
        'created_at'          => 'datetime',
        'updated_at'          => 'datetime',
    ];

    /**
     * Always scope to customer type.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('customer', function (Builder $builder) {
            $builder->where('type', 'customer');
        });

        static::creating(function (Customer $customer) {
            $customer->type = 'customer';
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'phone', 'status', 'is_blacklisted', 'deleted_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function addresses()
    {
        return $this->hasMany(PartyAddress::class, 'party_id');
    }

    public function defaultAddress()
    {
        return $this->hasOne(PartyAddress::class, 'party_id')->where('is_default', true);
    }

    public function orders()
    {
        // Bound when the Orders module is implemented
        if (class_exists(\App\Models\Order::class)) {
            return $this->hasMany(\App\Models\Order::class, 'party_id');
        }
        return $this->hasMany(self::class, 'id')->whereRaw('0=1'); // empty relation fallback
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')->where('is_active', true);
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('firstname', 'like', "%{$search}%")
              ->orWhere('lastname', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('gst_no', 'like', "%{$search}%")
              ->orWhere('company_name', 'like', "%{$search}%")
              ->orWhere('party_code', 'like', "%{$search}%");
        });
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────
    public function getNameAttribute(): string
    {
        return trim(collect([$this->firstname, $this->middlename, $this->lastname])
            ->filter()->implode(' '));
    }

    public function initials(): string
    {
        $first = $this->firstname[0] ?? '';
        $last  = $this->lastname[0]  ?? '';
        return strtoupper($first . $last);
    }
}
