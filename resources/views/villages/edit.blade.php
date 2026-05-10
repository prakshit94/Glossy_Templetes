<x-layouts.app pageTitle="Edit Village: {{ $village->village_name }}">
    <div class="p-6 lg:p-10">
        <div class="max-w-5xl mx-auto">
            <x-ui.card class="overflow-hidden border-border/40 shadow-2xl bg-white/[0.03] dark:bg-white/[0.02] backdrop-blur-2xl rounded-3xl">
                <div class="p-6 border-b border-white/10 bg-white/[0.02] flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold tracking-tight text-foreground">Edit Village Profile</h3>
                        <p class="text-xs text-muted-foreground mt-1">Manage geographical details and service availability.</p>
                    </div>
                </div>

                <form action="{{ route('villages.update', $village) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <x-ui.card-content class="p-8 space-y-12">
                        
                        <!-- SECTION: GEOGRAPHIC DETAILS -->
                        <div class="space-y-6">
                            <div class="flex items-center gap-2 pb-2 border-b border-border/40">
                                <x-ui.icon name="map" size="4" class="text-primary" />
                                <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground">Location Details</h4>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2 group">
                                    <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground">Village Name</label>
                                    <input type="text" name="village_name" value="{{ old('village_name', $village->village_name) }}" required 
                                        class="w-full px-4 py-2.5 rounded-xl bg-background/50 border border-border/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm">
                                </div>

                                <div class="space-y-2 group">
                                    <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground">Pincode</label>
                                    <input type="text" name="pincode" value="{{ old('pincode', $village->pincode) }}" required 
                                        class="w-full px-4 py-2.5 rounded-xl bg-background/50 border border-border/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="space-y-2">
                                    <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground">Post SO</label>
                                    <input type="text" name="post_so_name" value="{{ old('post_so_name', $village->post_so_name) }}" 
                                        class="w-full px-4 py-2.5 rounded-xl bg-background/50 border border-border/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground">Taluka</label>
                                    <input type="text" name="taluka_name" value="{{ old('taluka_name', $village->taluka_name) }}" 
                                        class="w-full px-4 py-2.5 rounded-xl bg-background/50 border border-border/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground">District</label>
                                    <input type="text" name="district_name" value="{{ old('district_name', $village->district_name) }}" 
                                        class="w-full px-4 py-2.5 rounded-xl bg-background/50 border border-border/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm">
                                </div>
                            </div>
                        </div>

                        <!-- SECTION: SERVICE MAPPINGS -->
                        <div class="space-y-6">
                            <div class="flex items-center gap-2 pb-2 border-b border-border/40">
                                <x-ui.icon name="truck" size="4" class="text-emerald-500" />
                                <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground">Service Availability Matrix</h4>
                            </div>

                            <div class="space-y-4">
                                @foreach($services as $service)
                                    @php $mapping = $mappings->get($service->id); @endphp
                                    <div class="p-6 rounded-3xl bg-white/[0.02] border border-white/5 hover:border-primary/20 transition-all duration-300">
                                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                                            <div class="flex items-center gap-4 min-w-[200px]">
                                                <div class="size-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary border border-primary/20 shadow-inner">
                                                    <x-ui.icon name="box" size="5" />
                                                </div>
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-bold text-foreground">{{ $service->name }}</span>
                                                    <span class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60">{{ $service->code }}</span>
                                                </div>
                                            </div>

                                            <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4">
                                                <div class="flex items-center gap-3">
                                                    <input type="checkbox" name="services[{{ $service->id }}][is_available]" 
                                                        value="1" {{ optional($mapping)->is_available ? 'checked' : '' }}
                                                        class="rounded border-border bg-background text-emerald-500 focus:ring-emerald-500/20 size-5">
                                                    <span class="text-xs font-bold text-muted-foreground">Available</span>
                                                </div>

                                                <div class="space-y-1">
                                                    <label class="text-[9px] font-black uppercase tracking-widest text-muted-foreground/40">Priority (0-99)</label>
                                                    <input type="number" name="services[{{ $service->id }}][priority]" 
                                                        value="{{ optional($mapping)->priority ?? 0 }}"
                                                        class="w-full px-3 py-1.5 rounded-lg bg-background border border-border/50 text-xs font-bold focus:ring-2 focus:ring-primary/20 outline-none">
                                                </div>

                                                <div class="space-y-1">
                                                    <label class="text-[9px] font-black uppercase tracking-widest text-muted-foreground/40">Internal Remarks</label>
                                                    <input type="text" name="services[{{ $service->id }}][remarks]" 
                                                        value="{{ optional($mapping)->remarks }}"
                                                        placeholder="e.g. Rainy season delays"
                                                        class="w-full px-3 py-1.5 rounded-lg bg-background border border-border/50 text-xs focus:ring-2 focus:ring-primary/20 outline-none">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </x-ui.card-content>
                    
                    <div class="p-8 border-t border-white/10 flex justify-end gap-3 bg-white/[0.01]">
                        <x-ui.button variant="outline" type="button" onclick="history.back()" class="rounded-2xl px-6">Cancel</x-ui.button>
                        <x-ui.button type="submit" class="rounded-2xl px-10 shadow-lg shadow-primary/20">Update Village & Services</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
