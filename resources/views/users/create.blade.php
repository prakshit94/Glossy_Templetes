<x-layouts.app pageTitle="Create New User">

    <div class="p-6 lg:p-10">
        <div class="max-w-3xl mx-auto">
            <x-ui.card>
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <x-ui.card-content class="space-y-6 pt-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2 group">
                                <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground group-focus-within:text-primary transition-colors">Full Name</label>
                                <div class="relative">
                                    <x-ui.icon name="user" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                    <input type="text" name="name" value="{{ old('name') }}" required 
                                        class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-background/50 border border-border/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm">
                                </div>
                                @error('name') <p class="text-[10px] font-bold text-destructive uppercase tracking-widest">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-2 group">
                                <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground group-focus-within:text-primary transition-colors">Username</label>
                                <div class="relative">
                                    <x-ui.icon name="at-sign" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                    <input type="text" name="username" value="{{ old('username') }}" required 
                                        class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-background/50 border border-border/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm">
                                </div>
                                @error('username') <p class="text-[10px] font-bold text-destructive uppercase tracking-widest">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="space-y-2 group">
                            <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground group-focus-within:text-primary transition-colors">Email Address</label>
                            <div class="relative">
                                <x-ui.icon name="mail" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                <input type="email" name="email" value="{{ old('email') }}" required 
                                    class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-background/50 border border-border/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm">
                            </div>
                            @error('email') <p class="text-[10px] font-bold text-destructive uppercase tracking-widest">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2 group">
                                <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground group-focus-within:text-primary transition-colors">Password</label>
                                <div class="relative">
                                    <x-ui.icon name="lock" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                    <input type="password" name="password" required 
                                        class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-background/50 border border-border/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm">
                                </div>
                                @error('password') <p class="text-[10px] font-bold text-destructive uppercase tracking-widest">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-2 group">
                                <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground group-focus-within:text-primary transition-colors">Confirm Password</label>
                                <div class="relative">
                                    <x-ui.icon name="check-circle" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                    <input type="password" name="password_confirmation" required 
                                        class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-background/50 border border-border/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2 group">
                                <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground group-focus-within:text-primary transition-colors">Status</label>
                                <div class="relative">
                                    <x-ui.icon name="activity" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors z-10" />
                                    <select name="status" class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-background/50 border border-border/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm appearance-none cursor-pointer">
                                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="suspended" {{ old('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                                    </select>
                                    <x-ui.icon name="chevron-down" size="4" class="absolute right-4 top-1/2 -translate-y-1/2 text-muted-foreground pointer-events-none" />
                                </div>
                            </div>
                            <div class="space-y-2 group">
                                <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground group-focus-within:text-primary transition-colors">Primary Team</label>
                                <div class="relative">
                                    <x-ui.icon name="briefcase" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors z-10" />
                                    <select name="current_team_id" class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-background/50 border border-border/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm appearance-none cursor-pointer">
                                        <option value="">No Team</option>
                                        @foreach($teams as $team)
                                            <option value="{{ $team->id }}" {{ old('current_team_id') == $team->id ? 'selected' : '' }}>{{ $team->name }}</option>
                                        @endforeach
                                    </select>
                                    <x-ui.icon name="chevron-down" size="4" class="absolute right-4 top-1/2 -translate-y-1/2 text-muted-foreground pointer-events-none" />
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground">Assign Roles</label>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3 p-4 bg-background/30 rounded-2xl border border-border/40 shadow-inner">
                                @foreach($roles as $role)
                                <label class="flex items-center gap-3 cursor-pointer group p-2 rounded-xl hover:bg-background/80 transition-all border border-transparent hover:border-border/50 hover:shadow-sm">
                                    <input type="checkbox" name="roles[]" value="{{ $role->name }}" 
                                        class="rounded-md border-border bg-background text-primary focus:ring-primary/20 size-4">
                                    <span class="text-xs font-bold text-muted-foreground group-hover:text-primary transition-colors">{{ $role->name }}</span>
                                </label>
                                @endforeach
                            </div>
                            @error('roles') <p class="text-[10px] font-bold text-destructive uppercase tracking-widest">{{ $message }}</p> @enderror
                        </div>
                    </x-ui.card-content>
                    <div class="p-6 border-t border-border/40 flex justify-end gap-3">
                        <x-ui.button variant="outline" type="button" onclick="history.back()">Cancel</x-ui.button>
                        <x-ui.button type="submit">Create User</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
