<x-layouts.app pageTitle="Create New Team">

    <div class="p-6 lg:p-10">
        <div class="max-w-3xl mx-auto">
            <x-ui.card>
                <form action="{{ route('teams.store') }}" method="POST">
                    @csrf
                    <x-ui.card-content class="space-y-6 pt-6">
                        <div class="space-y-2 group">
                            <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground group-focus-within:text-primary transition-colors">Team Name</label>
                            <div class="relative">
                                <x-ui.icon name="users" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                <input type="text" name="name" value="{{ old('name') }}" required 
                                    placeholder="e.g. Engineering, Marketing, Design..."
                                    class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-background/50 border border-border/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm">
                            </div>
                            @error('name') <p class="text-[10px] font-bold text-destructive uppercase tracking-widest">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2 group">
                            <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground group-focus-within:text-primary transition-colors">Description</label>
                            <div class="relative">
                                <x-ui.icon name="align-left" size="4" class="absolute left-3 top-3 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                <textarea name="description" rows="4"
                                    placeholder="Briefly describe the team's responsibilities..."
                                    class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-background/50 border border-border/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm">{{ old('description') }}</textarea>
                            </div>
                            @error('description') <p class="text-[10px] font-bold text-destructive uppercase tracking-widest">{{ $message }}</p> @enderror
                        </div>

                        <div class="p-4 rounded-xl bg-primary/5 border border-primary/10">
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 text-primary">
                                    <x-ui.icon name="info" size="4" />
                                </div>
                                <div class="text-xs text-muted-foreground">
                                    <span class="font-bold text-foreground">Note:</span> You will automatically be assigned as the Team Owner and granted the <span class="font-bold text-primary">admin</span> role for this new team.
                                </div>
                            </div>
                        </div>

                    </x-ui.card-content>
                    <div class="p-6 border-t border-border/40 flex justify-end gap-3">
                        <x-ui.button variant="outline" type="button" onclick="history.back()">Cancel</x-ui.button>
                        <x-ui.button type="submit">Create Team</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
