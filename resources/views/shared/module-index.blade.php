<x-layouts.app :pageTitle="$moduleTitle">
    <div class="p-6 lg:p-10" x-data="{ search: '', perPage: '10', isLoading: false }">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-primary/5 transition-all duration-500 overflow-hidden shadow-2xl">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-primary/10 blur-[50px] rounded-full group-hover:bg-primary/20 transition-all"></div>
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-primary/20 to-primary/5 border border-primary/10 text-primary flex items-center justify-center shadow-inner">
                        <x-ui.icon :name="$moduleIcon" size="7" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Total Records</p>
                        <div class="text-3xl font-black tracking-tighter text-foreground">0</div>
                    </div>
                </div>
            </div>
        </div>

        <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
            <x-ui.card-header class="border-b border-border/40 bg-muted/10 p-6">
                <div class="flex flex-col gap-4">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="size-12 rounded-2xl bg-primary/10 border border-primary/20 text-primary flex items-center justify-center shadow-inner">
                                <x-ui.icon :name="$moduleIcon" size="6" />
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-foreground tracking-tight">{{ $moduleTitle }}</h3>
                                <p class="text-[10px] uppercase font-bold text-muted-foreground tracking-widest">Module index scaffold</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
                            <x-ui.button variant="outline" size="sm" class="flex-1 sm:flex-none rounded-xl font-bold uppercase tracking-widest text-[10px] h-10 shadow-sm" onclick="alert('Import feature can be wired when module backend is ready.')">
                                <x-ui.icon name="upload" size="3" class="mr-2" />
                                Import
                            </x-ui.button>
                            <x-ui.button variant="outline" size="sm" class="flex-1 sm:flex-none rounded-xl font-bold uppercase tracking-widest text-[10px] h-10 shadow-sm" onclick="alert('Export feature can be wired when module backend is ready.')">
                                <x-ui.icon name="download" size="3" class="mr-2" />
                                Export
                            </x-ui.button>
                        </div>
                    </div>

                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pt-2">
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest hidden sm:inline-block">Show</span>
                            <select x-model="perPage" class="h-10 px-3 rounded-xl border border-border bg-background/50 text-xs font-medium focus:ring-1 focus:ring-primary outline-none">
                                <option value="5">5</option>
                                <option value="10">10</option>
                                <option value="15">15</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                            </select>
                        </div>
                        <div class="relative group w-full lg:max-w-xs shrink-0">
                            <x-ui.icon name="search" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                            <input type="text" x-model="search" placeholder="Search {{ strtolower($moduleTitle) }}..."
                                class="pl-9 pr-4 py-2 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 transition-all w-full text-xs shadow-sm outline-none">
                        </div>
                    </div>
                </div>
            </x-ui.card-header>

            <x-ui.card-content class="p-0">
                @includeFirst(
                    ["{$moduleKey}.partials.table", 'shared.partials.module-table'],
                    ['moduleTitle' => $moduleTitle, 'moduleIcon' => $moduleIcon]
                )
            </x-ui.card-content>
        </x-ui.card>
    </div>
</x-layouts.app>
