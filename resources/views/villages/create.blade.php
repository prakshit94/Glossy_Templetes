<x-layouts.app pageTitle="Add New Village">
    <div class="p-6 lg:p-10">
        <div class="max-w-4xl mx-auto">
            <x-ui.card class="overflow-hidden border-border/40 shadow-2xl bg-white/[0.03] dark:bg-white/[0.02] backdrop-blur-2xl rounded-3xl">
                <div class="p-6 border-b border-white/10 bg-white/[0.02]">
                    <h3 class="text-lg font-bold tracking-tight text-foreground">Village Registration</h3>
                    <p class="text-xs text-muted-foreground mt-1">Add a new geographical location to the service network.</p>
                </div>

                <form action="{{ route('villages.store') }}" method="POST">
                    @csrf
                    <x-ui.card-content class="p-8 space-y-10">
                        
                        <!-- SECTION: GEOGRAPHIC DETAILS -->
                        <div class="space-y-6">
                            <div class="flex items-center gap-2 pb-2 border-b border-border/40">
                                <x-ui.icon name="map" size="4" class="text-primary" />
                                <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground">Location Details</h4>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2 group">
                                    <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground group-focus-within:text-primary transition-colors">Village Name</label>
                                    <input type="text" name="village_name" value="{{ old('village_name') }}" required 
                                        placeholder="e.g. Rampur"
                                        class="w-full px-4 py-2.5 rounded-xl bg-background/50 border border-border/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm">
                                    @error('village_name') <p class="text-[10px] font-bold text-destructive uppercase tracking-widest">{{ $message }}</p> @enderror
                                </div>

                                <div class="space-y-2 group">
                                    <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground group-focus-within:text-primary transition-colors">Pincode</label>
                                    <input type="text" name="pincode" value="{{ old('pincode') }}" required 
                                        placeholder="400001"
                                        class="w-full px-4 py-2.5 rounded-xl bg-background/50 border border-border/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm">
                                    @error('pincode') <p class="text-[10px] font-bold text-destructive uppercase tracking-widest">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="space-y-2">
                                    <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground">Post SO Name</label>
                                    <input type="text" name="post_so_name" value="{{ old('post_so_name') }}" 
                                        class="w-full px-4 py-2.5 rounded-xl bg-background/50 border border-border/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground">Taluka</label>
                                    <input type="text" name="taluka_name" value="{{ old('taluka_name') }}" 
                                        class="w-full px-4 py-2.5 rounded-xl bg-background/50 border border-border/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground">District</label>
                                    <input type="text" name="district_name" value="{{ old('district_name') }}" 
                                        class="w-full px-4 py-2.5 rounded-xl bg-background/50 border border-border/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm">
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground">State</label>
                                <input type="text" name="state_name" value="{{ old('state_name') }}" 
                                    class="w-full px-4 py-2.5 rounded-xl bg-background/50 border border-border/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm">
                            </div>
                        </div>

                    </x-ui.card-content>
                    
                    <div class="p-8 border-t border-white/10 flex justify-end gap-3 bg-white/[0.01]">
                        <x-ui.button variant="outline" type="button" onclick="history.back()" class="rounded-2xl px-6">Cancel</x-ui.button>
                        <x-ui.button type="submit" class="rounded-2xl px-10 shadow-lg shadow-primary/20">Register Village</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
