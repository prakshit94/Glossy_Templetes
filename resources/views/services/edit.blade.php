<x-layouts.app>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('services.index') }}" class="inline-flex items-center justify-center size-10 rounded-2xl bg-muted border border-border/60 hover:bg-muted/80 transition-all duration-300 shadow-sm">
                <x-ui.icon name="chevron-left" size="4" class="text-foreground" />
            </a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Edit Service') }}: {{ $service->name }}
            </h2>
        </div>
    </x-slot>

    <div class="p-6 lg:p-10 max-w-4xl mx-auto">
        <x-ui.card class="border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl overflow-hidden">
            <x-ui.card-header class="border-b border-border/40 bg-muted/10 p-8">
                <div class="flex items-center gap-6">
                    <div class="size-16 rounded-2xl bg-gradient-to-tr from-primary/20 to-primary/5 border border-primary/10 text-primary flex items-center justify-center shadow-inner">
                        <x-ui.icon name="edit" size="8" />
                    </div>
                    <div>
                        <h3 class="text-2xl font-black tracking-tight text-foreground">Edit Service</h3>
                        <p class="text-sm text-muted-foreground">Modify service parameters and availability status.</p>
                    </div>
                </div>
            </x-ui.card-header>

            <x-ui.card-content class="p-8">
                <form action="{{ route('services.update', $service) }}" method="POST" class="space-y-8">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Service Name</label>
                            <div class="relative group">
                                <x-ui.icon name="tag" size="4" class="absolute left-4 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                <input type="text" name="name" value="{{ $service->name }}" required
                                    class="w-full pl-12 pr-4 py-3 rounded-2xl border border-border bg-background/50 focus:bg-background transition-all outline-none focus:ring-2 focus:ring-primary/20 text-foreground placeholder:text-muted-foreground/40">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Service Code</label>
                            <div class="relative group">
                                <x-ui.icon name="hash" size="4" class="absolute left-4 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                <input type="text" name="code" value="{{ $service->code }}" required
                                    class="w-full pl-12 pr-4 py-3 rounded-2xl border border-border bg-background/50 focus:bg-background transition-all outline-none focus:ring-2 focus:ring-primary/20 text-foreground placeholder:text-muted-foreground/40">
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Description</label>
                        <div class="relative group">
                            <textarea name="description" rows="4"
                                class="w-full p-4 rounded-2xl border border-border bg-background/50 focus:bg-background transition-all outline-none focus:ring-2 focus:ring-primary/20 resize-none text-foreground placeholder:text-muted-foreground/40">{{ $service->description }}</textarea>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-4 rounded-2xl bg-primary/5 border border-primary/10">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ $service->is_active ? 'checked' : '' }} class="rounded border-border text-primary focus:ring-primary/20 size-5 bg-background/50">
                        <label for="is_active" class="text-sm font-bold text-foreground cursor-pointer">Service is active and available for mapping</label>
                    </div>

                    <div class="flex items-center justify-end gap-4 pt-4 border-t border-border/40">
                        <a href="{{ route('services.index') }}">
                            <x-ui.button variant="ghost" type="button" class="font-bold uppercase tracking-widest text-[10px] hover:bg-muted text-muted-foreground">Cancel</x-ui.button>
                        </a>
                        <x-ui.button type="submit" class="rounded-xl px-8 font-bold uppercase tracking-widest text-[10px] shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all">
                            Update Service
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card-content>
        </x-ui.card>
    </div>
</x-layouts.app>
