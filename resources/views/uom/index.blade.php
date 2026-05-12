<x-layouts.app pageTitle="Units of Measure">

    <div class="p-6 lg:p-10" x-data="{ 
        stats: @js($stats),
        editingUom: null,

        openAddModal() {
            this.editingUom = null;
            $dispatch('open-modal', { name: 'uom-modal' });
        },

        openEditModal(uom) {
            this.editingUom = uom;
            $dispatch('open-modal', { name: 'uom-modal' });
        }
    }">

        <!-- Stats Widgets -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <x-ui.card class="p-6 bg-card/40 border-border/60 backdrop-blur-xl hover:bg-primary/5 transition-all duration-500 rounded-3xl group relative overflow-hidden">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-primary/10 blur-[50px] rounded-full group-hover:bg-primary/20 transition-all"></div>
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-primary/20 to-primary/5 border border-primary/10 text-primary flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="scale" size="7" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Total Units</p>
                        <div class="text-3xl font-black tracking-tighter text-foreground" x-text="stats.total"></div>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="p-6 bg-card/40 border-border/60 backdrop-blur-xl hover:bg-emerald-500/5 transition-all duration-500 rounded-3xl group relative overflow-hidden">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-emerald-500/10 blur-[50px] rounded-full group-hover:bg-emerald-500/20 transition-all"></div>
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-emerald-500/20 to-emerald-500/5 border border-emerald-500/10 text-emerald-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="check-circle" size="7" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Active Units</p>
                        <div class="text-3xl font-black tracking-tighter text-foreground" x-text="stats.active"></div>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card class="p-6 bg-card/40 border-border/60 backdrop-blur-xl hover:bg-violet-500/5 transition-all duration-500 rounded-3xl group relative overflow-hidden">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-violet-500/10 blur-[50px] rounded-full group-hover:bg-violet-500/20 transition-all"></div>
                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-violet-500/20 to-violet-500/5 border border-violet-500/10 text-violet-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="anchor" size="7" />
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">Base Units</p>
                        <div class="text-3xl font-black tracking-tighter text-foreground" x-text="stats.base"></div>
                    </div>
                </div>
            </x-ui.card>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                    <x-ui.card-header class="border-b border-border/40 bg-muted/10 p-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="size-10 rounded-2xl bg-primary/10 border border-primary/20 text-primary flex items-center justify-center shadow-inner">
                                    <x-ui.icon name="list" size="5" />
                                </div>
                                <h3 class="text-sm font-black text-foreground uppercase tracking-widest">Existing Units</h3>
                            </div>
                        </div>
                    </x-ui.card-header>
                    <x-ui.card-content class="p-0">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-muted/5 border-b border-border/40">
                                        <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Unit Name</th>
                                        <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80">Code</th>
                                        <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 text-center">Base Unit</th>
                                        <th class="p-4 text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($uoms as $uom)
                                        <tr class="border-b border-border/40 hover:bg-primary/[0.03] transition-colors group">
                                            <td class="p-4">
                                                <div class="text-sm font-bold text-foreground">{{ $uom->name }}</div>
                                            </td>
                                            <td class="p-4">
                                                <code class="px-2 py-1 rounded-lg bg-muted/30 text-[10px] font-bold text-muted-foreground">{{ $uom->code }}</code>
                                            </td>
                                            <td class="p-4 text-center">
                                                @if($uom->is_base_unit)
                                                    <x-ui.icon name="check-circle" size="4" class="text-emerald-500 mx-auto" />
                                                @else
                                                    <span class="text-[10px] text-muted-foreground/40">-</span>
                                                @endif
                                            </td>
                                            <td class="p-4 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    <button @click="openEditModal({{ json_encode($uom) }})" class="size-8 rounded-lg bg-background/50 border border-border/60 flex items-center justify-center text-muted-foreground hover:text-primary transition-all">
                                                        <x-ui.icon name="edit-3" size="3.5" />
                                                    </button>
                                                    <form action="{{ route('uoms.destroy', $uom) }}" method="POST" onsubmit="return confirm('Delete unit?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="size-8 rounded-lg bg-background/50 border border-border/60 flex items-center justify-center text-muted-foreground hover:text-destructive transition-all">
                                                            <x-ui.icon name="trash-2" size="3.5" />
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </x-ui.card-content>
                </x-ui.card>
            </div>

            <div>
                @include('uom.partials.form')
            </div>
        </div>
    </div>
</x-layouts.app>
