<x-layouts.app pageTitle="Edit Team: {{ $team->name }}">

    <div class="p-6 lg:p-10">
        <div class="max-w-3xl mx-auto">
            <x-ui.card>
                <form action="{{ route('teams.update', $team) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <x-ui.card-content class="space-y-6 pt-6">
                        <div class="space-y-2 group">
                            <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground group-focus-within:text-primary transition-colors">Team Name</label>
                            <div class="relative">
                                <x-ui.icon name="users" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                <input type="text" name="name" value="{{ old('name', $team->name) }}" required 
                                    class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-background/50 border border-border/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm">
                            </div>
                            @error('name') <p class="text-[10px] font-bold text-destructive uppercase tracking-widest">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2 group">
                            <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground group-focus-within:text-primary transition-colors">Description</label>
                            <div class="relative">
                                <x-ui.icon name="align-left" size="4" class="absolute left-3 top-3 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                <textarea name="description" rows="4"
                                    class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-background/50 border border-border/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm">{{ old('description', $team->description) }}</textarea>
                            </div>
                            @error('description') <p class="text-[10px] font-bold text-destructive uppercase tracking-widest">{{ $message }}</p> @enderror
                        </div>

                        <div class="p-4 rounded-xl bg-muted/20 border border-border/40">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="size-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold">
                                        {{ substr($team->owner->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest">Team Owner</p>
                                        <p class="text-sm font-bold text-foreground">{{ $team->owner->name }}</p>
                                    </div>
                                </div>
                                <div class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest bg-muted/50 px-2 py-1 rounded border border-border/40">
                                    Admin Controlled
                                </div>
                            </div>
                        </div>

                    </x-ui.card-content>
                    <div class="p-6 border-t border-border/40 flex justify-end gap-3">
                        <x-ui.button variant="outline" type="button" onclick="history.back()">Cancel</x-ui.button>
                        <x-ui.button type="submit">Update Team</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
