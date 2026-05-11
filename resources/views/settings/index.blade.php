<x-layouts.app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('System Settings') }}
        </h2>
    </x-slot>

    <div class="p-6 lg:p-10 space-y-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <!-- Left: Info -->
            <div class="space-y-3">
                <div class="size-12 rounded-2xl bg-primary/10 border border-primary/20 text-primary flex items-center justify-center shadow-inner">
                    <x-ui.icon name="settings" size="6" />
                </div>
                <h3 class="text-xl font-black tracking-tight text-foreground">General Configuration</h3>
                <p class="text-sm text-muted-foreground leading-relaxed">Manage your core application identity, global support details, and operational modes.</p>
            </div>

            <!-- Right: Form -->
            <div class="lg:col-span-2">
                <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                    <form action="{{ route('settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <x-ui.card-content class="p-8 space-y-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-2 group">
                                    <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground group-focus-within:text-primary transition-colors">Application Name</label>
                                    <input type="text" name="app_name" value="{{ config('app.name') }}" 
                                        class="w-full px-4 py-2.5 rounded-xl bg-background/50 border border-border focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm text-foreground font-bold">
                                </div>
                                <div class="space-y-2 group">
                                    <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground group-focus-within:text-primary transition-colors">Support Email Address</label>
                                    <input type="email" name="support_email" value="support@example.com" 
                                        class="w-full px-4 py-2.5 rounded-xl bg-background/50 border border-border focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm text-foreground font-bold">
                                </div>
                            </div>
                            
                            <div class="p-6 rounded-2xl bg-muted/10 border border-border/40 flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="size-10 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-600 flex items-center justify-center">
                                        <x-ui.icon name="tool" size="5" />
                                    </div>
                                    <div>
                                        <p class="text-xs font-black uppercase tracking-widest text-foreground">Maintenance Mode</p>
                                        <p class="text-[10px] text-muted-foreground">When active, only administrators can access the system.</p>
                                    </div>
                                </div>
                                <x-ui.checkbox id="maintenance_mode" name="maintenance_mode" class="size-6" />
                            </div>
                        </x-ui.card-content>
                        <div class="p-8 border-t border-border/40 bg-muted/10 flex justify-end">
                            <x-ui.button type="submit" class="rounded-2xl px-10 shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all">Save Global Settings</x-ui.button>
                        </div>
                    </form>
                </x-ui.card>
            </div>
        </div>

        <x-ui.separator class="opacity-40" />

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <!-- Left: Info -->
            <div class="space-y-3">
                <div class="size-12 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-600 flex items-center justify-center shadow-inner">
                    <x-ui.icon name="shield" size="6" />
                </div>
                <h3 class="text-xl font-black tracking-tight text-foreground">Security & Maintenance</h3>
                <p class="text-sm text-muted-foreground leading-relaxed">Directly manage system performance buffers and active security sessions.</p>
            </div>

            <!-- Right: Actions -->
            <div class="lg:col-span-2">
                <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                    <x-ui.card-content class="p-8 space-y-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-6 rounded-3xl bg-muted/10 border border-border/40 gap-6">
                            <div class="flex items-center gap-4">
                                <div class="size-12 rounded-2xl bg-blue-500/10 border border-blue-500/20 text-blue-600 flex items-center justify-center">
                                    <x-ui.icon name="database" size="6" />
                                </div>
                                <div>
                                    <p class="text-sm font-black uppercase tracking-widest text-foreground">System Cache Refresh</p>
                                    <p class="text-[10px] text-muted-foreground">Flushes application, config, and view cache layers.</p>
                                </div>
                            </div>
                            <form action="{{ route('settings.clear-cache') }}" method="POST" class="shrink-0">
                                @csrf
                                <x-ui.button variant="outline" size="sm" type="submit" class="rounded-xl border-border hover:bg-muted font-bold text-[10px] tracking-widest uppercase px-6">Flush Cache</x-ui.button>
                            </form>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-6 rounded-3xl bg-destructive/5 border border-destructive/20 gap-6">
                            <div class="flex items-center gap-4">
                                <div class="size-12 rounded-2xl bg-destructive/10 border border-destructive/20 text-destructive flex items-center justify-center">
                                    <x-ui.icon name="log-out" size="6" />
                                </div>
                                <div>
                                    <p class="text-sm font-black uppercase tracking-widest text-foreground">Emergency Session Termination</p>
                                    <p class="text-[10px] text-muted-foreground">Immediately terminates all active user sessions globally.</p>
                                </div>
                            </div>
                            <div class="shrink-0">
                                <x-ui.button variant="destructive" size="sm" class="rounded-xl font-bold text-[10px] tracking-widest uppercase px-6 shadow-lg shadow-destructive/20">Execute Purge</x-ui.button>
                            </div>
                        </div>
                    </x-ui.card-content>
                </x-ui.card>
            </div>
        </div>
    </div>
</x-layouts.app>
