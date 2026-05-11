<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'sku',
        'slug',
        'brand_id',
        'category_id',
        'tax_rate_id',
        'hsn_code_id',
        'uom_id',
        'barcode',
        'purchase_price',
        'mrp',
        'selling_price',
        'min_stock_level',
        'batch_tracking',
        'expiry_tracking',
        'allow_overselling',
        'manage_stock',
        'application_instructions',
        'status',
        'is_active',
        'description',
        'default_warehouse_id',
    ];

    protected $casts = [
        'batch_tracking' => 'boolean',
        'expiry_tracking' => 'boolean',
        'allow_overselling' => 'boolean',
        'manage_stock' => 'boolean',
        'is_active' => 'boolean',
        'purchase_price' => 'decimal:2',
        'mrp' => 'decimal:2',
        'selling_price' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class);
    }

    public function hsnCode(): BelongsTo
    {
        return $this->belongsTo(HsnCode::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'default_warehouse_id');
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class, 'uom_id');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function attributeValues()
    {
        return $this->belongsToMany(ProductAttributeValue::class, 'product_attribute_mapping', 'product_id', 'attribute_value_id');
    }

    public function getTotalStockAttribute()
    {
        return $this->stocks()->sum('quantity');
    }
}
