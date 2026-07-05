<x-layouts.app pageTitle="Product Details">

    {{-- Scoped Styles --}}
    @push('styles')
    <style>
        /* ── Glassmorphism Shimmer Card ────────────────────────────── */
        .product-hero-card {
            position: relative;
            overflow: hidden;
        }
        .product-hero-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, hsl(var(--primary) / 0.04) 0%, transparent 50%, hsl(var(--primary) / 0.02) 100%);
            pointer-events: none;
            z-index: 0;
        }
        .product-hero-card > * { position: relative; z-index: 1; }

        /* ── Subtle float animation for image ──────────────────────── */
        @keyframes floatY {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }
        .product-image-float {
            animation: floatY 4s ease-in-out infinite;
        }

        /* ── Fade-in stagger ───────────────────────────────────────── */
        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-in-up {
            animation: fadeSlideUp 0.5s ease-out both;
        }
        .fade-in-up:nth-child(1) { animation-delay: 0.05s; }
        .fade-in-up:nth-child(2) { animation-delay: 0.12s; }
        .fade-in-up:nth-child(3) { animation-delay: 0.19s; }
        .fade-in-up:nth-child(4) { animation-delay: 0.26s; }
        .fade-in-up:nth-child(5) { animation-delay: 0.33s; }
        .fade-in-up:nth-child(6) { animation-delay: 0.40s; }

        /* ── Info row hover ────────────────────────────────────────── */
        .info-row {
            transition: background 0.2s ease, transform 0.2s ease;
            border-radius: 0.75rem;
            padding: 0.625rem 0.75rem;
        }
        .info-row:hover {
            background: hsl(var(--muted) / 0.15);
            transform: translateX(4px);
        }

        /* ── Toggle pill ───────────────────────────────────────────── */
        .toggle-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.25rem 0.625rem;
            border-radius: 9999px;
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            transition: all 0.2s ease;
        }
        .toggle-pill.is-on {
            background: hsl(142 76% 36% / 0.12);
            color: hsl(142 76% 36%);
            border: 1px solid hsl(142 76% 36% / 0.25);
        }
        .toggle-pill.is-off {
            background: hsl(var(--muted) / 0.2);
            color: hsl(var(--muted-foreground) / 0.5);
            border: 1px solid hsl(var(--border) / 0.3);
        }
        .toggle-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
        }
        .toggle-pill.is-on .toggle-dot { background: hsl(142 76% 36%); }
        .toggle-pill.is-off .toggle-dot { background: hsl(var(--muted-foreground) / 0.3); }

        /* ── Stat box ──────────────────────────────────────────────── */
        .stat-box {
            padding: 1rem;
            border-radius: 1rem;
            border: 1px solid hsl(var(--border) / 0.3);
            background: hsl(var(--muted) / 0.06);
            transition: all 0.25s ease;
        }
        .stat-box:hover {
            border-color: hsl(var(--primary) / 0.3);
            background: hsl(var(--primary) / 0.04);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px hsl(var(--primary) / 0.06);
        }

        /* ── Price tag ─────────────────────────────────────────────── */
        .price-tag {
            background: linear-gradient(135deg, hsl(var(--primary) / 0.08), hsl(var(--primary) / 0.03));
            border: 1px solid hsl(var(--primary) / 0.15);
            border-radius: 1rem;
            padding: 1rem 1.25rem;
            transition: all 0.25s ease;
        }
        .price-tag:hover {
            border-color: hsl(var(--primary) / 0.3);
            box-shadow: 0 4px 16px hsl(var(--primary) / 0.08);
        }

        /* ── Warehouse row ─────────────────────────────────────────── */
        .warehouse-row {
            transition: all 0.2s ease;
        }
        .warehouse-row:hover {
            background: hsl(var(--muted) / 0.1);
        }

        /* ── Section label ─────────────────────────────────────────── */
        .section-label {
            font-size: 0.6rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: hsl(var(--muted-foreground) / 0.45);
        }

        /* ── Breadcrumb link ───────────────────────────────────────── */
        .breadcrumb-link {
            transition: color 0.2s ease;
        }
        .breadcrumb-link:hover {
            color: hsl(var(--primary));
        }
    </style>
    @endpush

    <div class="p-4 sm:p-6 lg:p-10 max-w-[1400px] mx-auto">
        @php
            $margin = $product->purchase_price > 0 ? round((($product->selling_price - $product->purchase_price) / $product->purchase_price) * 100, 1) : 0;
            
            if (!empty($product->grade)) {
                $grade = $product->grade;
                if ($grade === 'A') {
                    $gradeColor = 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20';
                    $gradeLabel = 'High Margin (Assigned)';
                } elseif ($grade === 'B') {
                    $gradeColor = 'bg-green-500/10 text-green-500 border-green-500/20';
                    $gradeLabel = 'Good Margin (Assigned)';
                } elseif ($grade === 'C') {
                    $gradeColor = 'bg-amber-500/10 text-amber-500 border-amber-500/20';
                    $gradeLabel = 'Average Margin (Assigned)';
                } else {
                    $gradeColor = 'bg-red-500/10 text-red-500 border-red-500/20';
                    $gradeLabel = 'Low Margin (Assigned)';
                }
            } else {
                $grade = 'D';
                $gradeColor = 'bg-red-500/10 text-red-500 border-red-500/20';
                $gradeLabel = 'Low Margin';
                
                if ($margin >= 50) {
                    $grade = 'A';
                    $gradeColor = 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20';
                    $gradeLabel = 'High Margin';
                } elseif ($margin >= 30) {
                    $grade = 'B';
                    $gradeColor = 'bg-green-500/10 text-green-500 border-green-500/20';
                    $gradeLabel = 'Good Margin';
                } elseif ($margin >= 10) {
                    $grade = 'C';
                    $gradeColor = 'bg-amber-500/10 text-amber-500 border-amber-500/20';
                    $gradeLabel = 'Average Margin';
                }
            }
        @endphp

        {{-- ════════════════════════════════════════════════════════════
             BREADCRUMB & PAGE HEADER
             ════════════════════════════════════════════════════════════ --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8 fade-in-up">
            {{-- Breadcrumb --}}
            <div>
                <div class="flex items-center gap-2 text-xs text-muted-foreground mb-1">
                    <a href="{{ route('products.index') }}" class="breadcrumb-link flex items-center gap-1 font-bold">
                        <x-ui.icon name="package" size="3" />
                        Products
                    </a>
                    <x-ui.icon name="chevron-right" size="3" class="opacity-30" />
                    <span class="font-black text-foreground truncate max-w-[200px]">{{ $product->name }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-foreground tracking-tight">{{ $product->name }}</h1>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('products.index') }}">
                    <x-ui.button variant="outline" class="h-10 rounded-xl font-bold text-xs gap-2 hover:bg-muted/30">
                        <x-ui.icon name="arrow-left" size="3.5" />
                        <span class="hidden sm:inline">Back</span>
                    </x-ui.button>
                </a>
                @can('products.edit')
                <a href="{{ route('products.edit', $product) }}">
                    <x-ui.button class="h-10 rounded-xl font-bold text-xs gap-2 shadow-lg shadow-primary/20">
                        <x-ui.icon name="edit-3" size="3.5" />
                        Edit Product
                    </x-ui.button>
                </a>
                @endcan
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════════
             MAIN GRID: 2/3 Left + 1/3 Right
             ════════════════════════════════════════════════════════════ --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">

            {{-- ═══════════════════════════════════════════════════════
                 LEFT COLUMN (2 cols)
                 ═══════════════════════════════════════════════════════ --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- ──────────────────────────────────────────────────
                     HERO CARD: Image + Core Info
                     ────────────────────────────────────────────────── --}}
                <x-ui.card class="product-hero-card overflow-hidden border-border/40 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl fade-in-up">
                    <div class="p-6 sm:p-8">
                        <div class="flex flex-col md:flex-row gap-6 md:gap-8">

                            {{-- Product Image --}}
                            <div class="w-full md:w-56 lg:w-64 shrink-0">
                                <div class="aspect-square rounded-2xl bg-gradient-to-br from-muted/20 to-muted/5 border border-border/40 overflow-hidden flex items-center justify-center product-image-float">
                                    @if($product->image_path)
                                        <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                    @else
                                        <x-ui.icon name="package" size="16" class="opacity-[0.06] text-primary" />
                                    @endif
                                </div>
                            </div>

                            {{-- Core Details --}}
                            <div class="flex-1 min-w-0 space-y-5">

                                {{-- Category Breadcrumb + Status --}}
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-2.5 py-1 rounded-lg bg-primary/10 border border-primary/20 text-[10px] font-black text-primary uppercase tracking-widest">
                                        {{ $product->category?->parent?->name ?? $product->category?->name ?? 'Uncategorized' }}
                                    </span>
                                    @if($product->category?->parent)
                                        <x-ui.icon name="chevron-right" size="3" class="text-muted-foreground opacity-20" />
                                        <span class="px-2.5 py-1 rounded-lg bg-muted/30 border border-border/40 text-[10px] font-black text-muted-foreground uppercase tracking-widest">
                                            {{ $product->category?->name }}
                                        </span>
                                    @endif

                                    {{-- Status Badge --}}
                                    @php
                                        $statusConfig = [
                                            'active' => ['bg' => 'bg-emerald-500/10', 'text' => 'text-emerald-500', 'border' => 'border-emerald-500/20', 'dot' => 'bg-emerald-500'],
                                            'draft' => ['bg' => 'bg-blue-500/10', 'text' => 'text-blue-500', 'border' => 'border-blue-500/20', 'dot' => 'bg-blue-500'],
                                            'out_of_stock' => ['bg' => 'bg-red-500/10', 'text' => 'text-red-500', 'border' => 'border-red-500/20', 'dot' => 'bg-red-500'],
                                        ];
                                        $sc = $statusConfig[$product->status] ?? ['bg' => 'bg-muted/40', 'text' => 'text-muted-foreground', 'border' => 'border-border/40', 'dot' => 'bg-muted-foreground'];
                                    @endphp
                                    <span class="ml-auto px-2.5 py-1 rounded-full border {{ $sc['bg'] }} {{ $sc['text'] }} {{ $sc['border'] }} text-[10px] font-black uppercase tracking-widest flex items-center gap-1.5">
                                        <span class="size-1.5 rounded-full {{ $sc['dot'] }} animate-pulse"></span>
                                        {{ str_replace('_', ' ', $product->status) }}
                                    </span>
                                </div>

                                {{-- Product Name --}}
                                <div>
                                    <div class="flex flex-wrap items-center gap-3">
                                        <h2 class="text-2xl sm:text-3xl font-black text-foreground leading-tight">{{ $product->name }}</h2>
                                        <div class="flex items-center gap-1.5 cursor-help" title="{{ $gradeLabel }}">
                                            <span class="px-2.5 py-1 rounded-lg border {{ $gradeColor }} text-[10px] font-black uppercase tracking-widest">
                                                Grade {{ $grade }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-3 mt-2">
                                        <span class="text-xs font-mono text-muted-foreground bg-muted/20 px-2 py-0.5 rounded-md border border-border/30">SKU: {{ $product->sku }}</span>
                                        @if($product->barcode)
                                            <span class="text-xs font-mono text-muted-foreground bg-muted/20 px-2 py-0.5 rounded-md border border-border/30">
                                                <x-ui.icon name="maximize" size="3" class="inline -mt-0.5 mr-0.5" />
                                                {{ $product->barcode }}
                                            </span>
                                        @endif
                                        @if($product->hsnCode)
                                            <span class="text-[9px] px-2 py-0.5 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-600 font-black uppercase">HSN: {{ $product->hsnCode->code }}</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Price Grid --}}
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                    <div class="price-tag">
                                        <p class="section-label mb-1">Selling Price</p>
                                        <p class="text-xl sm:text-2xl font-black text-primary">₹{{ number_format($product->selling_price, 2) }}</p>
                                    </div>
                                    <div class="price-tag">
                                        <p class="section-label mb-1">MRP</p>
                                        <p class="text-xl sm:text-2xl font-bold text-foreground/70">₹{{ number_format($product->mrp, 2) }}</p>
                                    </div>
                                    @role('Super Admin')
                                    <div class="price-tag">
                                        <p class="section-label mb-1">Purchase Price</p>
                                        <p class="text-xl sm:text-2xl font-bold text-foreground/60">₹{{ number_format($product->purchase_price, 2) }}</p>
                                    </div>
                                    @endrole
                                </div>

                                {{-- Tax Info --}}
                                @if($product->taxRate)
                                    <div class="flex items-center gap-2 text-xs">
                                        <span class="px-2 py-1 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 font-bold flex items-center gap-1.5">
                                            <x-ui.icon name="percent" size="3" />
                                            {{ $product->taxRate->name }} — {{ $product->taxRate->rate }}%
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </x-ui.card>

                {{-- ──────────────────────────────────────────────────
                     DETAILED SPECIFICATIONS
                     ────────────────────────────────────────────────── --}}
                <x-ui.card class="overflow-hidden border-border/40 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl fade-in-up">
                    <x-ui.card-header class="border-b border-border/30 bg-muted/5 px-6 py-4">
                        <div class="flex items-center gap-2">
                            <x-ui.icon name="list" size="4" class="text-primary opacity-60" />
                            <h3 class="text-xs font-black text-foreground uppercase tracking-widest">Product Specifications</h3>
                        </div>
                    </x-ui.card-header>
                    <x-ui.card-content class="p-4 sm:p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-0">
                            {{-- Brand --}}
                            <div class="info-row flex items-center justify-between">
                                <span class="text-xs text-muted-foreground/60 font-bold">Brand</span>
                                <span class="text-sm font-bold text-foreground">{{ $product->brand->name ?? '—' }}</span>
                            </div>
                            {{-- Category --}}
                            <div class="info-row flex items-center justify-between">
                                <span class="text-xs text-muted-foreground/60 font-bold">Category</span>
                                <span class="text-sm font-bold text-foreground">{{ $product->category?->name ?? '—' }}</span>
                            </div>
                            {{-- UoM --}}
                            <div class="info-row flex items-center justify-between">
                                <span class="text-xs text-muted-foreground/60 font-bold">Unit of Measure</span>
                                <span class="text-sm font-bold text-foreground">{{ $product->uom?->name ?? '—' }}</span>
                            </div>
                            {{-- Weight --}}
                            <div class="info-row flex items-center justify-between">
                                <span class="text-xs text-muted-foreground/60 font-bold">Weight</span>
                                <span class="text-sm font-bold text-foreground">{{ $product->weight ?? '—' }}</span>
                            </div>
                            {{-- Barcode --}}
                            <div class="info-row flex items-center justify-between">
                                <span class="text-xs text-muted-foreground/60 font-bold">Barcode</span>
                                <span class="text-sm font-mono font-bold text-foreground">{{ $product->barcode ?? '—' }}</span>
                            </div>
                            {{-- HSN Code --}}
                            <div class="info-row flex items-center justify-between">
                                <span class="text-xs text-muted-foreground/60 font-bold">HSN Code</span>
                                <span class="text-sm font-mono font-bold text-foreground">{{ $product->hsnCode?->code ?? '—' }}</span>
                            </div>
                            {{-- Tax Rate --}}
                            <div class="info-row flex items-center justify-between">
                                <span class="text-xs text-muted-foreground/60 font-bold">Tax Rate</span>
                                <span class="text-sm font-bold text-emerald-500">{{ $product->taxRate ? $product->taxRate->name . ' (' . $product->taxRate->rate . '%)' : '—' }}</span>
                            </div>
                            {{-- Default Warehouse --}}
                            <div class="info-row flex items-center justify-between">
                                <span class="text-xs text-muted-foreground/60 font-bold">Default Warehouse</span>
                                <span class="text-sm font-bold text-foreground">{{ $product->warehouse?->name ?? '—' }}</span>
                            </div>
                            {{-- Min Stock Level --}}
                            <div class="info-row flex items-center justify-between">
                                <span class="text-xs text-muted-foreground/60 font-bold">Min Stock Level</span>
                                <span class="text-sm font-black text-foreground">{{ $product->min_stock_level ?? 0 }}</span>
                            </div>
                            {{-- Slug --}}
                            <div class="info-row flex items-center justify-between">
                                <span class="text-xs text-muted-foreground/60 font-bold">Slug</span>
                                <span class="text-sm font-mono text-muted-foreground">{{ $product->slug ?? '—' }}</span>
                            </div>
                            {{-- Default Discount --}}
                            <div class="info-row flex items-center justify-between">
                                <span class="text-xs text-muted-foreground/60 font-bold">Default Discount</span>
                                <span class="text-sm font-bold text-foreground">
                                    @if($product->default_discount)
                                        {{ $product->default_discount }}{{ $product->default_discount_type === 'percent' ? '%' : ' (Flat)' }}
                                    @else
                                        —
                                    @endif
                                </span>
                            </div>
                            {{-- SKU Enabled --}}
                            <div class="info-row flex items-center justify-between">
                                <span class="text-xs text-muted-foreground/60 font-bold">SKU Enabled</span>
                                <span class="toggle-pill {{ $product->is_sku_enabled ? 'is-on' : 'is-off' }}">
                                    <span class="toggle-dot"></span>
                                    {{ $product->is_sku_enabled ? 'Yes' : 'No' }}
                                </span>
                            </div>
                        </div>
                    </x-ui.card-content>
                </x-ui.card>

                {{-- ──────────────────────────────────────────────────
                     INVENTORY & TRACKING SETTINGS
                     ────────────────────────────────────────────────── --}}
                <x-ui.card class="overflow-hidden border-border/40 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl fade-in-up">
                    <x-ui.card-header class="border-b border-border/30 bg-muted/5 px-6 py-4">
                        <div class="flex items-center gap-2">
                            <x-ui.icon name="settings" size="4" class="text-primary opacity-60" />
                            <h3 class="text-xs font-black text-foreground uppercase tracking-widest">Inventory & Tracking Settings</h3>
                        </div>
                    </x-ui.card-header>
                    <x-ui.card-content class="p-4 sm:p-6">
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                            {{-- Manage Stock --}}
                            <div class="stat-box text-center">
                                <p class="section-label mb-2">Manage Stock</p>
                                <span class="toggle-pill {{ $product->manage_stock ? 'is-on' : 'is-off' }}">
                                    <span class="toggle-dot"></span>
                                    {{ $product->manage_stock ? 'Enabled' : 'Disabled' }}
                                </span>
                            </div>
                            {{-- Batch Tracking --}}
                            <div class="stat-box text-center">
                                <p class="section-label mb-2">Batch Tracking</p>
                                <span class="toggle-pill {{ $product->batch_tracking ? 'is-on' : 'is-off' }}">
                                    <span class="toggle-dot"></span>
                                    {{ $product->batch_tracking ? 'Enabled' : 'Disabled' }}
                                </span>
                            </div>
                            {{-- Expiry Tracking --}}
                            <div class="stat-box text-center">
                                <p class="section-label mb-2">Expiry Tracking</p>
                                <span class="toggle-pill {{ $product->expiry_tracking ? 'is-on' : 'is-off' }}">
                                    <span class="toggle-dot"></span>
                                    {{ $product->expiry_tracking ? 'Enabled' : 'Disabled' }}
                                </span>
                            </div>
                            {{-- Allow Overselling --}}
                            <div class="stat-box text-center">
                                <p class="section-label mb-2">Overselling</p>
                                <span class="toggle-pill {{ $product->allow_overselling ? 'is-on' : 'is-off' }}">
                                    <span class="toggle-dot"></span>
                                    {{ $product->allow_overselling ? 'Allowed' : 'No' }}
                                </span>
                                @if($product->allow_overselling && $product->overselling_qty)
                                    <p class="text-[10px] text-muted-foreground mt-1.5 font-bold">Limit: {{ $product->overselling_qty }} units</p>
                                @endif
                            </div>
                        </div>
                    </x-ui.card-content>
                </x-ui.card>

                {{-- ──────────────────────────────────────────────────
                     PRODUCT ATTRIBUTES
                     ────────────────────────────────────────────────── --}}
                @if($product->attributeValues->count() > 0)
                <x-ui.card class="overflow-hidden border-border/40 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl fade-in-up">
                    <x-ui.card-header class="border-b border-border/30 bg-muted/5 px-6 py-4">
                        <div class="flex items-center gap-2">
                            <x-ui.icon name="sliders" size="4" class="text-primary opacity-60" />
                            <h3 class="text-xs font-black text-foreground uppercase tracking-widest">Product Attributes</h3>
                        </div>
                    </x-ui.card-header>
                    <x-ui.card-content class="p-4 sm:p-6">
                        <div class="flex flex-wrap gap-6">
                            @foreach($product->attributeValues->groupBy('attribute_id') as $attrId => $values)
                                <div class="space-y-2">
                                    <span class="section-label">{{ $values->first()?->attribute?->name ?? 'Attribute' }}</span>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($values as $val)
                                            <span class="px-3 py-1.5 rounded-xl bg-muted/20 border border-border/40 text-xs font-bold text-foreground flex items-center gap-2 transition-all hover:border-primary/30 hover:bg-primary/5">
                                                @if($val->attribute?->type === 'color')
                                                    <span class="size-3 rounded-full border border-border/40 shadow-sm" style="background-color: {{ $val->color_code }}"></span>
                                                @endif
                                                {{ $val->value }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </x-ui.card-content>
                </x-ui.card>
                @endif

                {{-- ──────────────────────────────────────────────────
                     DESCRIPTION & APPLICATION INSTRUCTIONS
                     ────────────────────────────────────────────────── --}}
                <x-ui.card class="overflow-hidden border-border/40 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl fade-in-up">
                    <x-ui.card-header class="border-b border-border/30 bg-muted/5 px-6 py-4">
                        <div class="flex items-center gap-2">
                            <x-ui.icon name="file-text" size="4" class="text-primary opacity-60" />
                            <h3 class="text-xs font-black text-foreground uppercase tracking-widest">Description</h3>
                        </div>
                    </x-ui.card-header>
                    <x-ui.card-content class="p-4 sm:p-6">
                        <p class="text-sm text-muted-foreground leading-relaxed">
                            {{ $product->description ?: 'No description provided.' }}
                        </p>
                    </x-ui.card-content>
                </x-ui.card>

                @if($product->application_instructions)
                <x-ui.card class="overflow-hidden border-border/40 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl fade-in-up">
                    <div class="p-6 sm:p-8 bg-gradient-to-br from-primary/5 to-transparent">
                        <div class="flex items-start gap-3">
                            <div class="size-10 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center shrink-0">
                                <x-ui.icon name="info" size="4.5" class="text-primary" />
                            </div>
                            <div>
                                <h4 class="text-[10px] font-black uppercase tracking-widest text-primary mb-2">Application Instructions & Dosage</h4>
                                <p class="text-sm text-foreground/80 leading-relaxed italic">
                                    "{{ $product->application_instructions }}"
                                </p>
                            </div>
                        </div>
                    </div>
                </x-ui.card>
                @endif

                {{-- ──────────────────────────────────────────────────
                     STOCK AVAILABILITY BY WAREHOUSE
                     ────────────────────────────────────────────────── --}}
                <x-ui.card class="overflow-hidden border-border/40 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl fade-in-up">
                    <x-ui.card-header class="border-b border-border/30 bg-muted/5 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <x-ui.icon name="database" size="4" class="text-primary opacity-60" />
                                <h3 class="text-xs font-black text-foreground uppercase tracking-widest">Stock by Warehouse</h3>
                            </div>
                            <span class="px-2.5 py-1 rounded-full bg-primary/10 border border-primary/20 text-[10px] font-black text-primary">
                                {{ $product->stocks->count() }} {{ Str::plural('warehouse', $product->stocks->count()) }}
                            </span>
                        </div>
                    </x-ui.card-header>
                    <x-ui.card-content class="p-0">
                        @if($product->stocks->count() > 0)
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-muted/5 border-b border-border/30">
                                    <th class="px-6 py-3 section-label">Warehouse</th>
                                    <th class="px-6 py-3 section-label text-center">Quantity</th>
                                    <th class="px-6 py-3 section-label text-center">Reserved</th>
                                    <th class="px-6 py-3 section-label text-center">Dispatched</th>
                                    <th class="px-6 py-3 section-label text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($product->stocks as $stock)
                                <tr class="warehouse-row border-b border-border/20 last:border-b-0">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="size-8 rounded-lg bg-muted/20 border border-border/30 flex items-center justify-center">
                                                <x-ui.icon name="home" size="3.5" class="text-muted-foreground/50" />
                                            </div>
                                            <span class="text-sm font-bold text-foreground">{{ $stock->warehouse?->name ?? 'Unknown' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center font-black text-lg text-primary">{{ number_format($stock->quantity) }}</td>
                                    <td class="px-6 py-4 text-center font-bold text-sm text-amber-500">{{ number_format($stock->reserved_qty ?? 0) }}</td>
                                    <td class="px-6 py-4 text-center font-bold text-sm text-blue-500">{{ number_format($stock->dispatched_qty ?? 0) }}</td>
                                    <td class="px-6 py-4 text-right">
                                        @php $qty = $stock->quantity - ($stock->reserved_qty ?? 0); @endphp
                                        @if($qty > 0)
                                            <span class="px-2 py-1 rounded-full bg-emerald-500/10 text-emerald-500 border border-emerald-500/20 text-[10px] font-black uppercase">In Stock</span>
                                        @else
                                            <span class="px-2 py-1 rounded-full bg-red-500/10 text-red-500 border border-red-500/20 text-[10px] font-black uppercase">Out of Stock</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @else
                            <div class="p-8 text-center">
                                <x-ui.icon name="inbox" size="8" class="mx-auto opacity-10 text-muted-foreground mb-3" />
                                <p class="text-sm text-muted-foreground/60 font-bold">No stock entries found</p>
                            </div>
                        @endif
                    </x-ui.card-content>
                </x-ui.card>

            </div>

            {{-- ═══════════════════════════════════════════════════════
                 RIGHT COLUMN (1 col) — Sidebar
                 ═══════════════════════════════════════════════════════ --}}
            <div class="space-y-6">

                {{-- ──────────────────────────────────────────────────
                     STOCK SUMMARY CARD
                     ────────────────────────────────────────────────── --}}
                <x-ui.card class="overflow-hidden border-border/40 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl fade-in-up">
                    <x-ui.card-header class="border-b border-border/30 bg-muted/5 px-5 py-4">
                        <div class="flex items-center gap-2">
                            <x-ui.icon name="activity" size="4" class="text-primary opacity-60" />
                            <h4 class="text-[10px] font-black uppercase tracking-widest text-foreground">Stock Overview</h4>
                        </div>
                    </x-ui.card-header>
                    <div class="p-5 space-y-3">
                        <div class="stat-box">
                            <p class="section-label mb-1">Total Stock</p>
                            <p class="text-2xl font-black text-primary">{{ number_format($product->total_stock) }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="stat-box">
                                <p class="section-label mb-1">Reserved</p>
                                <p class="text-lg font-black text-amber-500">{{ number_format($product->total_reserved) }}</p>
                            </div>
                            <div class="stat-box">
                                <p class="section-label mb-1">Dispatched</p>
                                <p class="text-lg font-black text-blue-500">{{ number_format($product->total_dispatched) }}</p>
                            </div>
                        </div>
                        <div class="stat-box border-primary/20 bg-primary/5">
                            <p class="section-label mb-1">Available Stock</p>
                            <p class="text-2xl font-black text-emerald-500">{{ number_format($product->available_stock) }}</p>
                        </div>
                    </div>
                </x-ui.card>

                {{-- ──────────────────────────────────────────────────
                     QUICK FACTS CARD
                     ────────────────────────────────────────────────── --}}
                <x-ui.card class="overflow-hidden border-border/40 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl fade-in-up">
                    <x-ui.card-header class="border-b border-border/30 bg-muted/5 px-5 py-4">
                        <div class="flex items-center gap-2">
                            <x-ui.icon name="star" size="4" class="text-primary opacity-60" />
                            <h4 class="text-[10px] font-black uppercase tracking-widest text-foreground">Quick Facts</h4>
                        </div>
                    </x-ui.card-header>
                    <div class="p-5 space-y-0">
                        <div class="info-row flex items-center justify-between">
                            <span class="text-[10px] font-bold text-muted-foreground/60 uppercase">Brand</span>
                            <span class="text-xs font-black text-foreground">{{ $product->brand->name ?? 'No Brand' }}</span>
                        </div>
                        <div class="info-row flex items-center justify-between">
                            <span class="text-[10px] font-bold text-muted-foreground/60 uppercase">HSN Code</span>
                            <span class="text-xs font-mono font-bold text-foreground">{{ $product->hsnCode?->code ?? 'N/A' }}</span>
                        </div>
                        <div class="info-row flex items-center justify-between">
                            <span class="text-[10px] font-bold text-muted-foreground/60 uppercase">Tax Class</span>
                            <span class="text-xs font-bold text-emerald-500">{{ $product->taxRate ? $product->taxRate->name : 'Tax Exempt' }}</span>
                        </div>
                        <div class="info-row flex items-center justify-between">
                            <span class="text-[10px] font-bold text-muted-foreground/60 uppercase">UoM</span>
                            <span class="text-xs font-bold text-foreground">{{ $product->uom?->name ?? 'N/A' }}</span>
                        </div>
                        <div class="info-row flex items-center justify-between">
                            <span class="text-[10px] font-bold text-muted-foreground/60 uppercase">Weight</span>
                            <span class="text-xs font-bold text-foreground">{{ $product->weight ?? 'N/A' }}</span>
                        </div>
                        <div class="info-row flex items-center justify-between">
                            <span class="text-[10px] font-bold text-muted-foreground/60 uppercase">Min Stock</span>
                            <span class="text-xs font-black text-foreground">{{ $product->min_stock_level ?? 0 }}</span>
                        </div>
                        @if($product->default_discount)
                        <div class="info-row flex items-center justify-between">
                            <span class="text-[10px] font-bold text-muted-foreground/60 uppercase">Discount</span>
                            <span class="text-xs font-black text-primary">
                                {{ $product->default_discount }}{{ $product->default_discount_type === 'percent' ? '%' : ' ₹' }}
                            </span>
                        </div>
                        @endif
                    </div>
                </x-ui.card>

                @if($product->activeOffers->count() > 0 || (isset($storeWideOffers) && $storeWideOffers->count() > 0) || (float) $product->default_discount > 0)
                {{-- ──────────────────────────────────────────────────
                     ACTIVE OFFERS & DISCOUNTS
                     ────────────────────────────────────────────────── --}}
                <x-ui.card class="overflow-hidden border-rose-500/30 shadow-2xl bg-rose-500/[0.02] backdrop-blur-2xl rounded-3xl fade-in-up">
                    <x-ui.card-header class="border-b border-rose-500/20 bg-rose-500/5 px-5 py-4">
                        <div class="flex items-center gap-2">
                            <x-ui.icon name="tag" size="4" class="text-rose-500 opacity-80" />
                            <h4 class="text-[10px] font-black uppercase tracking-widest text-rose-600">Available Offers</h4>
                        </div>
                    </x-ui.card-header>
                    <div class="p-5 space-y-4">
                        {{-- Product Base Discount --}}
                        @if((float) $product->default_discount > 0)
                            <div class="space-y-2">
                                <p class="text-[9px] font-black uppercase tracking-widest text-emerald-500/60">Base Offer</p>
                                <div class="p-3.5 rounded-2xl bg-card/60 border border-emerald-500/10 hover:border-emerald-500/30 hover:bg-emerald-500/[0.04] transition-all">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="space-y-1 min-w-0">
                                            <span class="text-xs font-black text-foreground block truncate">Product Base Discount</span>
                                            <p class="text-[9px] font-bold text-muted-foreground/80 uppercase tracking-widest">Applied automatically to this product at checkout</p>
                                        </div>
                                        <span class="text-[10px] font-black text-emerald-500 bg-emerald-500/10 border border-emerald-500/20 px-2 py-0.5 rounded-lg uppercase tracking-wider shrink-0">
                                            {{ rtrim(rtrim(number_format((float) $product->default_discount, 2), '0'), '.') }}{{ $product->default_discount_type === 'percent' ? '%' : '₹' }} OFF
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Product-Specific Campaigns --}}
                        @if($product->activeOffers->count() > 0)
                            <div class="space-y-2">
                                <p class="text-[9px] font-black uppercase tracking-widest text-rose-500/60">Product Campaigns</p>
                                @foreach($product->activeOffers as $offer)
                                    <div class="p-3.5 rounded-2xl bg-card/60 border border-rose-500/10 hover:border-rose-500/30 hover:bg-rose-500/[0.04] transition-all">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="space-y-1 min-w-0">
                                                <span class="text-xs font-black text-foreground block truncate" title="{{ $offer->name }}">{{ $offer->name }}</span>
                                                @if($offer->type === 'bogo')
                                                    <p class="text-[9px] font-bold text-muted-foreground uppercase tracking-widest">
                                                        Buy {{ $offer->buy_qty }} Get {{ $offer->get_qty }} free
                                                    </p>
                                                @endif
                                                @if($offer->min_spend > 0)
                                                    <p class="text-[9px] font-bold text-muted-foreground/80 uppercase tracking-widest">Min Spend: ₹{{ number_format($offer->min_spend, 2) }}</p>
                                                @endif
                                                @if($offer->ends_at)
                                                    <p class="text-[9px] font-bold text-orange-500 uppercase tracking-widest">Ends: {{ $offer->ends_at->format('d M Y, h:i A') }}</p>
                                                @endif
                                            </div>
                                            <span class="text-[10px] font-black text-rose-500 bg-rose-500/10 border border-rose-500/20 px-2 py-0.5 rounded-lg uppercase tracking-wider shrink-0">
                                                @if($offer->type === 'bogo')
                                                    BOGO
                                                @else
                                                    {{ $offer->discount_type === 'percentage' ? rtrim(rtrim(number_format((float) $offer->value, 2), '0'), '.') . '%' : '₹' . number_format((float) $offer->value, 2) }} OFF
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Store-Wide Campaigns --}}
                        @if(isset($storeWideOffers) && $storeWideOffers->count() > 0)
                            <div class="space-y-2 pt-2 border-t border-border/40">
                                <p class="text-[9px] font-black uppercase tracking-widest text-blue-500/60">Global Store Offers</p>
                                @foreach($storeWideOffers as $offer)
                                    <div class="p-3.5 rounded-2xl bg-card/60 border border-blue-500/10 hover:border-blue-500/30 hover:bg-blue-500/[0.04] transition-all">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="space-y-1 min-w-0">
                                                <span class="text-xs font-black text-foreground block truncate" title="{{ $offer->name }}">{{ $offer->name }}</span>
                                                @if($offer->min_spend > 0)
                                                    <p class="text-[9px] font-bold text-muted-foreground/80 uppercase tracking-widest">Min Spend: ₹{{ number_format($offer->min_spend, 2) }}</p>
                                                @endif
                                                @if($offer->ends_at)
                                                    <p class="text-[9px] font-bold text-orange-500 uppercase tracking-widest">Ends: {{ $offer->ends_at->format('d M Y, h:i A') }}</p>
                                                @endif
                                            </div>
                                            <span class="text-[10px] font-black text-blue-500 bg-blue-500/10 border border-blue-500/20 px-2 py-0.5 rounded-lg uppercase tracking-wider shrink-0">
                                                {{ $offer->discount_type === 'percentage' ? rtrim(rtrim(number_format((float) $offer->value, 2), '0'), '.') . '%' : '₹' . number_format((float) $offer->value, 2) }} OFF
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </x-ui.card>
                @endif

                {{-- ──────────────────────────────────────────────────
                     PRICING BREAKDOWN
                     ────────────────────────────────────────────────── --}}
                <x-ui.card class="overflow-hidden border-border/40 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl fade-in-up">
                    <x-ui.card-header class="border-b border-border/30 bg-muted/5 px-5 py-4">
                        <div class="flex items-center gap-2">
                            <x-ui.icon name="dollar-sign" size="4" class="text-primary opacity-60" />
                            <h4 class="text-[10px] font-black uppercase tracking-widest text-foreground">Pricing Breakdown</h4>
                        </div>
                    </x-ui.card-header>
                    <div class="p-5 space-y-0">
                        @role('Super Admin')
                        <div class="info-row flex items-center justify-between">
                            <span class="text-[10px] font-bold text-muted-foreground/60 uppercase">Purchase Price</span>
                            <span class="text-sm font-black text-foreground">₹{{ number_format($product->purchase_price, 2) }}</span>
                        </div>
                        @endrole
                        <div class="info-row flex items-center justify-between">
                            <span class="text-[10px] font-bold text-muted-foreground/60 uppercase">Selling Price</span>
                            <span class="text-sm font-black text-primary">₹{{ number_format($product->selling_price, 2) }}</span>
                        </div>
                        <div class="info-row flex items-center justify-between">
                            <span class="text-[10px] font-bold text-muted-foreground/60 uppercase">MRP</span>
                            <span class="text-sm font-bold text-foreground/70">₹{{ number_format($product->mrp, 2) }}</span>
                        </div>
                        @if($product->taxRate)
                        <div class="info-row flex items-center justify-between">
                            <span class="text-[10px] font-bold text-muted-foreground/60 uppercase">Tax ({{ $product->taxRate->name }})</span>
                            <span class="text-sm font-bold text-emerald-500">{{ $product->taxRate->rate }}%</span>
                        </div>
                        @endif
                        @role('Super Admin')
                        <div class="mt-3 pt-3 border-t border-border/20 space-y-0">
                            <div class="info-row flex items-center justify-between">
                                <span class="text-[10px] font-bold text-muted-foreground/60 uppercase">Margin</span>
                                <span class="text-sm font-black {{ $margin >= 0 ? 'text-emerald-500' : 'text-red-500' }}">
                                    {{ $margin >= 0 ? '+' : '' }}{{ $margin }}%
                                </span>
                            </div>
                        </div>
                        @endrole
                    </div>
                </x-ui.card>

                {{-- ──────────────────────────────────────────────────
                     TIMESTAMPS
                     ────────────────────────────────────────────────── --}}
                <x-ui.card class="overflow-hidden border-border/40 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl fade-in-up">
                    <div class="p-5 space-y-0">
                        <div class="info-row flex items-center justify-between">
                            <span class="text-[10px] font-bold text-muted-foreground/60 uppercase">Created</span>
                            <span class="text-xs font-bold text-muted-foreground">{{ $product->created_at?->format('d M Y, h:i A') ?? '—' }}</span>
                        </div>
                        <div class="info-row flex items-center justify-between">
                            <span class="text-[10px] font-bold text-muted-foreground/60 uppercase">Last Updated</span>
                            <span class="text-xs font-bold text-muted-foreground">{{ $product->updated_at?->format('d M Y, h:i A') ?? '—' }}</span>
                        </div>
                    </div>
                </x-ui.card>
            </div>

        </div>
    </div>

</x-layouts.app>
