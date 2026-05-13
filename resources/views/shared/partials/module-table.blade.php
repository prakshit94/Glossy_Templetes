<x-ui.table>
    <x-ui.table-header class="bg-muted/20">
        <x-ui.table-row class="border-b border-border/60">
            <x-ui.table-head class="w-14">#</x-ui.table-head>
            <x-ui.table-head>{{ $moduleTitle }} Name</x-ui.table-head>
            <x-ui.table-head>Status</x-ui.table-head>
            <x-ui.table-head>Last Updated</x-ui.table-head>
            <x-ui.table-head class="text-right">Actions</x-ui.table-head>
        </x-ui.table-row>
    </x-ui.table-header>
    <x-ui.table-body>
        <x-ui.table-row>
            <x-ui.table-cell colspan="5" class="h-56 text-center">
                <div class="flex flex-col items-center justify-center gap-3 opacity-40">
                    <x-ui.icon :name="$moduleIcon" size="12" />
                    <p class="text-sm font-black uppercase tracking-[0.2em]">No {{ strtolower($moduleTitle) }} records found</p>
                    <p class="text-[10px] font-semibold text-muted-foreground uppercase tracking-widest">Scaffold ready for module integration</p>
                </div>
            </x-ui.table-cell>
        </x-ui.table-row>
    </x-ui.table-body>
</x-ui.table>
