{{-- ══ TAB: Overview / Profile ══ --}}
<div x-show="activeTab === 'overview'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-cloak>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT COL: Personal Info --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Identity Card --}}
            <div class="bg-card/60 backdrop-blur-xl border border-border/50 rounded-3xl shadow-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-border/40 bg-muted/10 flex items-center gap-2">
                    <x-ui.icon name="user" size="4" class="text-primary" />
                    <h3 class="text-xs font-black uppercase tracking-widest text-foreground">Personal Information</h3>
                </div>
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-5">
                    @foreach([
                        ['Full Name', $customer->name, 'user'],
                        ['Email', $customer->email ?: '—', 'mail'],
                        ['Phone', $customer->phone ?: '—', 'phone'],
                        ['Alternate Mobile', $customer->alternatemobile ?? '—', 'phone-call'],
                        ['Relative Contact', ($customer->relative_mobile ?? '—') . ($customer->relative_phone ? " ({$customer->relative_phone})" : ''), 'users'],
                        ['Secondary Phone', $customer->phone_number_2 ?? '—', 'phone-call'],
                        ['Customer ID', '#'.sprintf('%04d',$customer->id), 'hash'],
                        ['Status', ucfirst($customer->status), 'activity'],
                        ['Registered On', $customer->created_at->format('M d, Y'), 'calendar'],
                    ] as [$label, $val, $icon])
                    <div class="flex items-start gap-3">
                        <div class="size-8 rounded-xl bg-muted/50 flex items-center justify-center text-muted-foreground shrink-0 mt-0.5">
                            <x-ui.icon name="{{ $icon }}" size="3.5" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">{{ $label }}</p>
                            <p class="text-sm font-bold text-foreground truncate mt-0.5">{{ $val }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Tax & Business Info --}}
            <div class="bg-card/60 backdrop-blur-xl border border-border/50 rounded-3xl shadow-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-border/40 bg-muted/10 flex items-center gap-2">
                    <x-ui.icon name="file-text" size="4" class="text-blue-500" />
                    <h3 class="text-xs font-black uppercase tracking-widest text-foreground">Tax & Business Info</h3>
                </div>
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-5">
                    @foreach([
                        ['GST Number',    $customer->gst_no       ?: '—', 'shield'],
                        ['PAN Number',    $customer->pan_no       ?: '—', 'credit-card'],
                        ['Aadhaar Last4', $customer->aadhaar_last4 ?? '—', 'lock'],
                        ['Company Name',  $customer->company_name  ?? '—', 'briefcase'],
                        ['Party Code',    $customer->party_code    ?? '—', 'hash'],
                        ['Category',      ucfirst($customer->category ?? '—'), 'tag'],
                        ['Source',        is_array($customer->source) ? implode(', ', $customer->source) : ($customer->source ?: '—'), 'compass'],
                        ['KYC Status',    $customer->kyc_completed ? 'Verified ✓' : 'Pending', 'shield'],
                    ] as [$label, $val, $icon])
                    <div class="flex items-start gap-3">
                        <div class="size-8 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-500 shrink-0 mt-0.5">
                            <x-ui.icon name="{{ $icon }}" size="3.5" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">{{ $label }}</p>
                            <p class="text-sm font-bold text-foreground font-mono mt-0.5">{{ $val }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Agriculture Profile Section --}}
            <div class="bg-card/60 backdrop-blur-xl border border-border/50 rounded-3xl shadow-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-border/40 bg-muted/10 flex items-center gap-2">
                    <x-ui.icon name="sun" size="4" class="text-amber-500" />
                    <h3 class="text-xs font-black uppercase tracking-widest text-foreground">Agriculture Profile</h3>
                </div>
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-6">
                    <div class="flex items-start gap-3">
                        <div class="size-8 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-500 shrink-0 mt-0.5">
                            <x-ui.icon name="maximize" size="3.5" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Land Area</p>
                            <p class="text-sm font-bold text-foreground mt-0.5">{{ $customer->land_area ?? 0 }} {{ $customer->land_unit ?? 'Acre' }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="size-8 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-500 shrink-0 mt-0.5">
                            <x-ui.icon name="droplets" size="3.5" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Irrigation Type</p>
                            <p class="text-sm font-bold text-foreground mt-0.5">
                                {{ is_array($customer->irrigation_type) ? implode(', ', $customer->irrigation_type) : ($customer->irrigation_type ?: '—') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 sm:col-span-2">
                        <div class="size-8 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-500 shrink-0 mt-0.5">
                            <x-ui.icon name="leaf" size="3.5" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-2">Major Crops Cultivated</p>
                            <div class="flex flex-wrap gap-2">
                                @forelse($customer->crops ?? [] as $crop)
                                    <span class="px-2.5 py-1 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 text-[10px] font-black uppercase tracking-wider">
                                        {{ $crop }}
                                    </span>
                                @empty
                                    <span class="text-sm font-bold text-muted-foreground italic">No crops recorded</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT COL: Stats + System --}}
        <div class="space-y-6">
            {{-- Quick Stats --}}
            <div class="bg-card/60 backdrop-blur-xl border border-border/50 rounded-3xl shadow-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-border/40 bg-muted/10 flex items-center gap-2">
                    <x-ui.icon name="bar-chart-2" size="4" class="text-emerald-500" />
                    <h3 class="text-xs font-black uppercase tracking-widest text-foreground">Quick Stats</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center gap-4 p-4 rounded-2xl bg-emerald-500/5 border border-emerald-500/10">
                        <div class="size-12 rounded-2xl bg-emerald-500/10 flex items-center justify-center shrink-0">
                            <x-ui.icon name="shopping-bag" size="5" class="text-emerald-500" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Total Orders</p>
                            <p class="text-3xl font-black text-foreground">{{ $customer->orders()->count() }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-4 rounded-2xl bg-purple-500/5 border border-purple-500/10">
                        <div class="size-12 rounded-2xl bg-purple-500/10 flex items-center justify-center shrink-0">
                            <x-ui.icon name="map-pin" size="5" class="text-purple-500" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Addresses</p>
                            <p class="text-3xl font-black text-foreground">{{ $customer->addresses->count() }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-4 rounded-2xl bg-primary/5 border border-primary/10">
                        <div class="size-12 rounded-2xl bg-primary/10 flex items-center justify-center shrink-0">
                            <x-ui.icon name="credit-card" size="5" class="text-primary" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Credit Limit</p>
                            <p class="text-2xl font-black text-foreground">₹{{ number_format($customer->credit_limit ?? 0) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- System Info --}}
            <div class="bg-card/60 backdrop-blur-xl border border-border/50 rounded-3xl shadow-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-border/40 bg-muted/10 flex items-center gap-2">
                    <x-ui.icon name="settings" size="4" class="text-muted-foreground" />
                    <h3 class="text-xs font-black uppercase tracking-widest text-foreground">System Info</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Created At</p>
                        <p class="text-sm font-bold text-foreground mt-0.5">{{ $customer->created_at->format('M d, Y — h:i A') }}</p>
                        <p class="text-[11px] text-muted-foreground">{{ $customer->created_at->diffForHumans() }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground">Last Updated</p>
                        <p class="text-sm font-bold text-foreground mt-0.5">{{ $customer->updated_at->format('M d, Y — h:i A') }}</p>
                        <p class="text-[11px] text-muted-foreground">{{ $customer->updated_at->diffForHumans() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
