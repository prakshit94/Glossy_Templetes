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
        'name',
        'firstname',
        'middlename',
        'lastname',
        'type',
        'email',
        'phone',
        'alternatemobile',
        'relative_mobile',
        'gst_no',
        'pan_no',
        'tax_no',
        'credit_limit',
        'credit_days',
        'status',
        'is_active',
        'account_type_id',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'credit_days'  => 'integer',
        'is_active'    => 'boolean',
        'created_at'   => 'datetime',
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
            ->logOnly(['name', 'email', 'phone', 'status', 'deleted_at'])
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
        return $query->where('status', 'active');
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('gst_no', 'like', "%{$search}%");
        });
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function initials(): string
    {
        $words = explode(' ', $this->name);
        return strtoupper(collect($words)->take(2)->map(fn($w) => $w[0] ?? '')->implode(''));
    }
}
