<x-layouts.app pageTitle="Permission Reference">

    <div class="p-6 lg:p-10">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($permissions as $group => $groupPermissions)
            <x-ui.card class="h-fit overflow-hidden border-border/40 shadow-sm hover:shadow-md transition-shadow">
                <x-ui.card-header class="bg-muted/10 border-b border-border/40 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="size-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary shadow-inner">
                            <x-ui.icon name="folder" size="5" />
                        </div>
                        <div>
                            <x-ui.card-title class="text-sm font-black uppercase tracking-widest text-primary">{{ $group }}</x-ui.card-title>
                            <x-ui.card-description class="text-[10px]">Access nodes for {{ $group }}</x-ui.card-description>
                        </div>
                    </div>
                </x-ui.card-header>
                <x-ui.card-content class="p-0">
                    <div class="divide-y divide-border/30">
                        @foreach($groupPermissions as $permission)
                        <div class="px-5 py-3.5 flex items-center justify-between group hover:bg-primary/5 transition-all">
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-foreground group-hover:text-primary transition-colors">{{ ucwords(str_replace(['.', '_', '-'], ' ', str_replace($group . '.', '', $permission->name))) }}</span>
                                <span class="text-[9px] font-medium text-muted-foreground font-mono">{{ $permission->name }}</span>
                            </div>
                            <div class="size-6 rounded-lg bg-muted flex items-center justify-center group-hover:bg-primary group-hover:text-primary-foreground transition-all shadow-sm">
                                <x-ui.icon name="key" size="3" />
                            </div>
                        </div>
                        @endforeach
                    </div>
                </x-ui.card-content>
            </x-ui.card>
            @endforeach
        </div>
    </div>
</x-layouts.app>
