{{-- ══ TAB: System ══ --}}
<div x-show="activeTab === 'system'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-cloak>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <x-ui.card class="p-8 rounded-3xl border-border/40 shadow-xl bg-card/60 backdrop-blur-xl">
            <h4 class="text-[11px] font-black uppercase tracking-[0.2em] text-muted-foreground mb-8 flex items-center gap-2">
                <span class="size-2 rounded-full bg-orange-500 inline-block"></span> Timestamps
            </h4>
            <div class="space-y-6">
                <div class="flex items-center gap-4">
                    <div class="size-12 rounded-xl bg-muted flex items-center justify-center shrink-0">
                        <x-ui.icon name="calendar" size="5" class="text-muted-foreground" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-1">Created On</p>
                        <p class="text-sm font-bold text-foreground">{{ $customer->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="size-12 rounded-xl bg-muted flex items-center justify-center shrink-0">
                        <x-ui.icon name="refresh-cw" size="5" class="text-muted-foreground" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground mb-1">Last Updated</p>
                        <p class="text-sm font-bold text-foreground">{{ $customer->updated_at->diffForHumans() }}</p>
                    </div>
                </div>
            </div>
        </x-ui.card>

        <div class="p-8 rounded-3xl border-2 border-destructive/20 bg-destructive/5 relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 size-40 bg-destructive/10 rounded-full blur-2xl"></div>
            <h4 class="text-[11px] font-black uppercase tracking-[0.2em] text-destructive mb-6 flex items-center gap-2 relative z-10">
                <span class="size-2 rounded-full bg-destructive inline-block shadow-lg shadow-destructive/50 animate-pulse"></span> Danger Zone
            </h4>
            <p class="text-sm font-bold text-foreground relative z-10">Archive Customer Record</p>
            <p class="text-xs text-muted-foreground mt-2 mb-8 relative z-10">This action will hide the customer from main views but can be restored by an admin.</p>
            
            <form action="{{ route('customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('Archive this customer?')" class="relative z-10">
                @csrf
                @method('DELETE')
                <button type="submit" class="h-12 px-6 rounded-xl bg-destructive/10 text-destructive text-xs font-black uppercase tracking-widest hover:bg-destructive hover:text-destructive-foreground transition-all duration-300 border border-destructive/20 hover:border-destructive hover:shadow-xl hover:shadow-destructive/30 flex items-center gap-2">
                    <x-ui.icon name="archive" size="4" /> Archive Record
                </button>
            </form>
        </div>
    </div>
</div>
