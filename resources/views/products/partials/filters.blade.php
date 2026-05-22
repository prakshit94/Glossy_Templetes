<!-- Category (X/Y) -->
<div class="relative" x-data="{ open: false, filter: '' }">
    <button type="button" @click="open = !open" class="h-10 px-4 flex items-center rounded-2xl border border-border bg-background/50 text-[11px] font-black hover:bg-background transition-all group shadow-sm uppercase tracking-widest">
        <span class="text-muted-foreground/80 group-hover:text-primary transition-colors">Categories</span>
        <span class="ml-2 px-1.5 py-0.5 rounded-lg bg-primary/10 text-primary font-black text-[10px]">
            <span x-text="categoryFilter.length"></span>/<span x-text="categoriesList.length"></span>
        </span>
        <x-ui.icon name="chevron-down" size="3" class="ml-2 text-muted-foreground/40" />
    </button>
    <div x-show="open" @click.away="open = false" x-cloak class="absolute left-0 mt-2 w-72 bg-popover border border-border rounded-2xl shadow-2xl z-[100] p-2">
        <div class="p-2 border-b border-border bg-muted/10 mb-1">
            <input type="text" x-model="filter" placeholder="Search..." class="w-full px-3 h-9 bg-background rounded-xl border border-border text-[11px] outline-none">
        </div>
        <div class="max-h-60 overflow-y-auto custom-scrollbar">
            <template x-for="item in categoriesList.filter(i => i.name.toLowerCase().includes(filter.toLowerCase()))" :key="item.slug">
                <label class="flex items-center gap-2 px-3 py-2 rounded-xl hover:bg-muted cursor-pointer transition-colors" x-bind:class="categoryFilter.includes(item.slug) ? 'bg-primary/5 border border-primary/10' : ''">
                    <input type="checkbox" :value="item.slug" x-model="categoryFilter" @change="performSearch()" class="rounded border-border text-primary">
                    <span class="text-[11px] font-medium" x-text="item.name"></span>
                </label>
            </template>
        </div>
    </div>
</div>

<!-- Status (X/Y) -->
<div class="relative" x-data="{ open: false, filter: '' }">
    <button type="button" @click="open = !open" class="h-10 px-4 flex items-center rounded-2xl border border-border bg-background/50 text-[11px] font-black hover:bg-background transition-all group shadow-sm uppercase tracking-widest">
        <span class="text-muted-foreground/80 group-hover:text-emerald-500 transition-colors">Status</span>
        <span class="ml-2 px-1.5 py-0.5 rounded-lg bg-emerald-500/10 text-emerald-500 font-black text-[10px]">
            <span x-text="statusFilter.length"></span>/<span x-text="statusList.length"></span>
        </span>
        <x-ui.icon name="chevron-down" size="3" class="ml-2 text-muted-foreground/40" />
    </button>
    <div x-show="open" @click.away="open = false" x-cloak class="absolute left-0 mt-2 w-72 bg-popover border border-border rounded-2xl shadow-2xl z-[100] p-2">
        <div class="p-2 border-b border-border bg-muted/10 mb-1">
            <input type="text" x-model="filter" placeholder="Search..." class="w-full px-3 h-9 bg-background rounded-xl border border-border text-[11px] outline-none">
        </div>
        <div class="max-h-60 overflow-y-auto custom-scrollbar">
            <template x-for="item in statusList.filter(i => i.label.toLowerCase().includes(filter.toLowerCase()))" :key="item.value">
                <label class="flex items-center gap-2 px-3 py-2 rounded-xl hover:bg-muted cursor-pointer transition-colors" x-bind:class="statusFilter.includes(item.value) ? 'bg-emerald-500/5 border border-emerald-500/10' : ''">
                    <input type="checkbox" :value="item.value" x-model="statusFilter" @change="performSearch()" class="rounded border-border text-primary">
                    <span class="text-[11px] font-medium" x-text="item.label"></span>
                </label>
            </template>
        </div>
    </div>
</div>
