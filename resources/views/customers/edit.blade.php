<x-layouts.app pageTitle="Edit Customer: {{ $customer->name }}">

    <div class="p-6 lg:p-10">
        <div class="max-w-4xl mx-auto">
            <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                <div class="p-6 border-b border-border/40 bg-muted/10">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="size-12 rounded-2xl bg-gradient-to-tr from-primary/20 to-primary/5 border border-primary/10 text-primary flex items-center justify-center shadow-inner font-black text-lg">
                                {{ $customer->initials() }}
                            </div>
                            <div>
                                <h3 class="text-lg font-bold tracking-tight text-foreground">{{ $customer->name }}</h3>
                                <p class="text-xs text-muted-foreground mt-0.5">Customer #{{ sprintf('%04d', $customer->id) }} · Registered {{ $customer->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                        <a href="{{ route('customers.index') }}">
                            <x-ui.button variant="outline" size="sm" class="rounded-xl border-border text-muted-foreground hover:bg-muted">
                                <x-ui.icon name="arrow-left" size="3" class="mr-2" />
                                Back to List
                            </x-ui.button>
                        </a>
                    </div>
                </div>

                <form action="{{ route('customers.update', $customer) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <x-ui.card-content class="p-8 space-y-8">

                        {{-- ─── Basic Info ──────────────────────────────────────────────────────── --}}
                        <div>
                            <div class="flex items-center gap-2 pb-3 mb-6 border-b border-border/40">
                                <x-ui.icon name="user" size="4" class="text-primary" />
                                <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground">Basic Information</h4>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2 group">
                                    <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground group-focus-within:text-primary transition-colors">
                                        Full Name <span class="text-destructive">*</span>
                                    </label>
                                    <div class="relative">
                                        <x-ui.icon name="user" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                        <input type="text" name="name" value="{{ old('name', $customer->name) }}" required
                                            class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-background/50 border border-border focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm text-foreground">
                                    </div>
                                    @error('name') <p class="text-[10px] font-bold text-destructive uppercase tracking-widest">{{ $message }}</p> @enderror
                                </div>

                                <div class="space-y-2 group">
                                    <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground group-focus-within:text-primary transition-colors">
                                        Account Status <span class="text-destructive">*</span>
                                    </label>
                                    <div class="relative">
                                        <x-ui.icon name="activity" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground z-10" />
                                        <select name="status" class="w-full pl-10 pr-10 py-2.5 rounded-xl bg-background/50 border border-border focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm appearance-none cursor-pointer text-foreground font-bold">
                                            <option value="active"    {{ old('status', $customer->status) === 'active'    ? 'selected' : '' }}>Active</option>
                                            <option value="inactive"  {{ old('status', $customer->status) === 'inactive'  ? 'selected' : '' }}>Inactive</option>
                                            <option value="suspended" {{ old('status', $customer->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                                        </select>
                                        <x-ui.icon name="chevron-down" size="4" class="absolute right-4 top-1/2 -translate-y-1/2 text-muted-foreground pointer-events-none" />
                                    </div>
                                    @error('status') <p class="text-[10px] font-bold text-destructive uppercase tracking-widest">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- ─── Contact Info ────────────────────────────────────────────────────── --}}
                        <div>
                            <div class="flex items-center gap-2 pb-3 mb-6 border-b border-border/40">
                                <x-ui.icon name="phone" size="4" class="text-primary" />
                                <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground">Contact Details</h4>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2 group">
                                    <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground group-focus-within:text-primary transition-colors">Email Address</label>
                                    <div class="relative">
                                        <x-ui.icon name="mail" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                        <input type="email" name="email" value="{{ old('email', $customer->email) }}"
                                            placeholder="customer@example.com"
                                            class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-background/50 border border-border focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm text-foreground">
                                    </div>
                                    @error('email') <p class="text-[10px] font-bold text-destructive uppercase tracking-widest">{{ $message }}</p> @enderror
                                </div>

                                <div class="space-y-2 group">
                                    <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground group-focus-within:text-primary transition-colors">Phone Number</label>
                                    <div class="relative">
                                        <x-ui.icon name="phone" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                        <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}"
                                            placeholder="+91 98765 43210"
                                            class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-background/50 border border-border focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm text-foreground">
                                    </div>
                                    @error('phone') <p class="text-[10px] font-bold text-destructive uppercase tracking-widest">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- ─── Tax Information ─────────────────────────────────────────────────── --}}
                        <div>
                            <div class="flex items-center gap-2 pb-3 mb-6 border-b border-border/40">
                                <x-ui.icon name="hash" size="4" class="text-primary" />
                                <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground">Tax & Compliance</h4>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2 group">
                                    <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground group-focus-within:text-primary transition-colors">GST Number</label>
                                    <div class="relative">
                                        <x-ui.icon name="hash" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                        <input type="text" name="gst_no" value="{{ old('gst_no', $customer->gst_no) }}"
                                            placeholder="22AAAAA0000A1Z5"
                                            class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-background/50 border border-border focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm text-foreground font-mono uppercase">
                                    </div>
                                    @error('gst_no') <p class="text-[10px] font-bold text-destructive uppercase tracking-widest">{{ $message }}</p> @enderror
                                </div>

                                <div class="space-y-2 group">
                                    <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground group-focus-within:text-primary transition-colors">PAN Number</label>
                                    <div class="relative">
                                        <x-ui.icon name="hash" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                        <input type="text" name="pan_no" value="{{ old('pan_no', $customer->pan_no) }}"
                                            placeholder="AAAAA0000A"
                                            class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-background/50 border border-border focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm text-foreground font-mono uppercase">
                                    </div>
                                    @error('pan_no') <p class="text-[10px] font-bold text-destructive uppercase tracking-widest">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- ─── Credit Terms ─────────────────────────────────────────────────────── --}}
                        <div>
                            <div class="flex items-center gap-2 pb-3 mb-6 border-b border-border/40">
                                <x-ui.icon name="credit-card" size="4" class="text-primary" />
                                <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground">Credit Terms</h4>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2 group">
                                    <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground group-focus-within:text-primary transition-colors">Credit Limit (₹)</label>
                                    <div class="relative">
                                        <x-ui.icon name="credit-card" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                        <input type="number" name="credit_limit" value="{{ old('credit_limit', $customer->credit_limit) }}" min="0" step="0.01"
                                            class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-background/50 border border-border focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm text-foreground">
                                    </div>
                                    @error('credit_limit') <p class="text-[10px] font-bold text-destructive uppercase tracking-widest">{{ $message }}</p> @enderror
                                </div>

                                <div class="space-y-2 group">
                                    <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground group-focus-within:text-primary transition-colors">Credit Days</label>
                                    <div class="relative">
                                        <x-ui.icon name="calendar" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                        <input type="number" name="credit_days" value="{{ old('credit_days', $customer->credit_days) }}" min="0"
                                            class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-background/50 border border-border focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm text-foreground">
                                    </div>
                                    @error('credit_days') <p class="text-[10px] font-bold text-destructive uppercase tracking-widest">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- ─── Customer Metadata ───────────────────────────────────────────────── --}}
                        <div class="p-6 rounded-3xl bg-muted/10 border border-border/40 space-y-4 shadow-inner">
                            <p class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground flex items-center gap-2">
                                <x-ui.icon name="info" size="4" class="text-blue-500" />
                                Customer Record Details
                            </p>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="p-4 rounded-2xl bg-background border border-border/60 shadow-sm">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Customer ID</p>
                                    <p class="text-xs font-bold text-foreground font-mono">#{{ sprintf('%04d', $customer->id) }}</p>
                                </div>
                                <div class="p-4 rounded-2xl bg-background border border-border/60 shadow-sm">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Registered On</p>
                                    <p class="text-xs font-bold text-foreground">{{ $customer->created_at->format('M d, Y') }}</p>
                                    <p class="text-[9px] text-muted-foreground/50 mt-0.5">{{ $customer->created_at->diffForHumans() }}</p>
                                </div>
                                <div class="p-4 rounded-2xl bg-background border border-border/60 shadow-sm">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Last Updated</p>
                                    <p class="text-xs font-bold text-foreground">{{ $customer->updated_at->format('M d, Y') }}</p>
                                    <p class="text-[9px] text-muted-foreground/50 mt-0.5">{{ $customer->updated_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        </div>

                    </x-ui.card-content>

                    <div class="p-8 border-t border-border/40 flex justify-between items-center bg-muted/10 rounded-b-3xl">
                        <form action="{{ route('customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('Move this customer to archive?')">
                            @csrf
                            @method('DELETE')
                            <x-ui.button type="submit" variant="ghost" class="rounded-xl text-destructive hover:bg-destructive/10 border border-transparent hover:border-destructive/20">
                                <x-ui.icon name="trash" size="4" class="mr-2" />
                                Archive Customer
                            </x-ui.button>
                        </form>
                        <div class="flex gap-3">
                            <x-ui.button variant="outline" type="button" onclick="history.back()" class="rounded-2xl px-6 border-border hover:bg-muted text-muted-foreground">Cancel</x-ui.button>
                            <x-ui.button type="submit" class="rounded-2xl px-10 shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all">
                                <x-ui.icon name="check" size="4" class="mr-2" />
                                Update Customer
                            </x-ui.button>
                        </div>
                    </div>
                </form>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
