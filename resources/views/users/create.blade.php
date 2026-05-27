<x-layouts.app pageTitle="Create New User">

    <div class="p-6 lg:p-10">
        <div class="max-w-4xl mx-auto">
            <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                <div class="p-6 border-b border-border/40 bg-muted/10">
                    <h3 class="text-lg font-bold tracking-tight text-foreground">Account Creation</h3>
                    <p class="text-xs text-muted-foreground mt-1">Register a new member with specific roles and system permissions.</p>
                </div>

                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <x-ui.card-content class="p-8 space-y-8">

                        {{-- ── Name Parts ── --}}
                        <div>
                            <div class="flex items-center gap-2 pb-2 mb-4 border-b border-border/40">
                                <x-ui.icon name="user" size="4" class="text-primary" />
                                <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground">Personal Information</h4>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                {{-- First Name (required) --}}
                                <div class="space-y-2 group">
                                    <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground group-focus-within:text-primary transition-colors">
                                        First Name <span class="text-destructive">*</span>
                                    </label>
                                    <div class="relative">
                                        <x-ui.icon name="user" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                        <input type="text" name="firstname" value="{{ old('firstname') }}" required
                                            placeholder="e.g. John"
                                            class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-background/50 border border-border focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm text-foreground">
                                    </div>
                                    @error('firstname') <p class="text-[10px] font-bold text-destructive uppercase tracking-widest">{{ $message }}</p> @enderror
                                </div>

                                {{-- Middle Name (optional) --}}
                                <div class="space-y-2 group">
                                    <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground group-focus-within:text-primary transition-colors">
                                        Middle Name <span class="text-muted-foreground/50 normal-case font-medium">(optional)</span>
                                    </label>
                                    <div class="relative">
                                        <x-ui.icon name="user" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                        <input type="text" name="middlename" value="{{ old('middlename') }}"
                                            placeholder="e.g. William"
                                            class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-background/50 border border-border focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm text-foreground">
                                    </div>
                                    @error('middlename') <p class="text-[10px] font-bold text-destructive uppercase tracking-widest">{{ $message }}</p> @enderror
                                </div>

                                {{-- Last Name (optional) --}}
                                <div class="space-y-2 group">
                                    <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground group-focus-within:text-primary transition-colors">
                                        Last Name <span class="text-muted-foreground/50 normal-case font-medium">(optional)</span>
                                    </label>
                                    <div class="relative">
                                        <x-ui.icon name="user" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                        <input type="text" name="lastname" value="{{ old('lastname') }}"
                                            placeholder="e.g. Doe"
                                            class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-background/50 border border-border focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm text-foreground">
                                    </div>
                                    @error('lastname') <p class="text-[10px] font-bold text-destructive uppercase tracking-widest">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- ── Account Details ── --}}
                        <div>
                            <div class="flex items-center gap-2 pb-2 mb-4 border-b border-border/40">
                                <x-ui.icon name="mail" size="4" class="text-primary" />
                                <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground">Account Details</h4>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Email (required) --}}
                                <div class="space-y-2 group">
                                    <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground group-focus-within:text-primary transition-colors">
                                        Email Address <span class="text-destructive">*</span>
                                    </label>
                                    <div class="relative">
                                        <x-ui.icon name="mail" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                        <input type="email" name="email" value="{{ old('email') }}" required
                                            placeholder="john@example.com"
                                            class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-background/50 border border-border focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm text-foreground">
                                    </div>
                                    @error('email') <p class="text-[10px] font-bold text-destructive uppercase tracking-widest">{{ $message }}</p> @enderror
                                </div>

                                {{-- Username (optional) --}}
                                <div class="space-y-2 group">
                                    <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground group-focus-within:text-primary transition-colors">
                                        Username <span class="text-muted-foreground/50 normal-case font-medium">(optional — auto-generated if blank)</span>
                                    </label>
                                    <div class="relative">
                                        <x-ui.icon name="at-sign" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                        <input type="text" name="username" value="{{ old('username') }}"
                                            placeholder="johndoe123"
                                            class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-background/50 border border-border focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm text-foreground">
                                    </div>
                                    @error('username') <p class="text-[10px] font-bold text-destructive uppercase tracking-widest">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- ── Avatar & Bio ── --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Avatar URL (optional) --}}
                            <div class="space-y-2 group">
                                <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground group-focus-within:text-primary transition-colors">
                                    Avatar URL <span class="text-muted-foreground/50 normal-case font-medium">(optional)</span>
                                </label>
                                <div class="relative">
                                    <x-ui.icon name="image" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                    <input type="url" name="avatar" value="{{ old('avatar') }}"
                                        placeholder="https://example.com/avatar.jpg"
                                        class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-background/50 border border-border focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm text-foreground">
                                </div>
                                @error('avatar') <p class="text-[10px] font-bold text-destructive uppercase tracking-widest">{{ $message }}</p> @enderror
                            </div>

                            {{-- Bio (optional) --}}
                            <div class="space-y-2 group">
                                <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground group-focus-within:text-primary transition-colors">
                                    Bio <span class="text-muted-foreground/50 normal-case font-medium">(optional)</span>
                                </label>
                                <div class="relative">
                                    <x-ui.icon name="align-left" size="4" class="absolute left-3 top-3 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                    <textarea name="bio" rows="1"
                                        placeholder="Short bio about this user..."
                                        class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-background/50 border border-border focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm text-foreground resize-none">{{ old('bio') }}</textarea>
                                </div>
                                @error('bio') <p class="text-[10px] font-bold text-destructive uppercase tracking-widest">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- ── Password ── --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2 group">
                                <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground group-focus-within:text-primary transition-colors">
                                    Password <span class="text-destructive">*</span>
                                </label>
                                <div class="relative">
                                    <x-ui.icon name="lock" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                    <input type="password" name="password" required
                                        class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-background/50 border border-border focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm text-foreground">
                                </div>
                                @error('password') <p class="text-[10px] font-bold text-destructive uppercase tracking-widest">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-2 group">
                                <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground group-focus-within:text-primary transition-colors">
                                    Confirm Password <span class="text-destructive">*</span>
                                </label>
                                <div class="relative">
                                    <x-ui.icon name="check-circle" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                    <input type="password" name="password_confirmation" required
                                        class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-background/50 border border-border focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm text-foreground">
                                </div>
                            </div>
                        </div>

                        {{-- ── Status & Primary Team ── --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2 group">
                                <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground group-focus-within:text-primary transition-colors">Account Status</label>
                                <div class="relative">
                                    <x-ui.icon name="activity" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors z-10" />
                                    <select name="status" class="w-full pl-10 pr-10 py-2.5 rounded-xl bg-background/50 border border-border focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm appearance-none cursor-pointer text-foreground font-bold">
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
                                    <select name="current_team_id" class="w-full pl-10 pr-10 py-2.5 rounded-xl bg-background/50 border border-border focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm appearance-none cursor-pointer text-foreground font-bold">
                                        <option value="">No Team Assigned</option>
                                        @foreach($teams as $team)
                                            <option value="{{ $team->id }}" {{ old('current_team_id') == $team->id ? 'selected' : '' }}>{{ $team->name }}</option>
                                        @endforeach
                                    </select>
                                    <x-ui.icon name="chevron-down" size="4" class="absolute right-4 top-1/2 -translate-y-1/2 text-muted-foreground pointer-events-none" />
                                </div>
                            </div>
                        </div>

                        {{-- ── Roles ── --}}
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 pb-2 border-b border-border/40">
                                <x-ui.icon name="shield" size="4" class="text-primary" />
                                <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-muted-foreground">Permission Profiles (Roles)</h4>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3 p-6 bg-muted/10 rounded-3xl border border-border/40 shadow-inner">
                                @foreach($roles as $role)
                                <label class="flex items-center gap-3 cursor-pointer group p-3 rounded-xl hover:bg-card/50 transition-all border border-transparent hover:border-border/50 hover:shadow-sm">
                                    <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                                        {{ in_array($role->name, old('roles', [])) ? 'checked' : '' }}
                                        class="rounded border-border bg-background text-primary focus:ring-primary/20 size-5">
                                    <span class="text-xs font-bold text-muted-foreground group-hover:text-foreground transition-colors uppercase tracking-tight">{{ $role->name }}</span>
                                </label>
                                @endforeach
                            </div>
                            @error('roles') <p class="text-[10px] font-bold text-destructive uppercase tracking-widest">{{ $message }}</p> @enderror
                        </div>

                    </x-ui.card-content>

                    <div class="p-8 border-t border-border/40 flex justify-end gap-3 bg-muted/10 rounded-b-3xl">
                        <x-ui.button variant="outline" type="button" onclick="history.back()" class="rounded-2xl px-6 border-border hover:bg-muted text-muted-foreground">Cancel</x-ui.button>
                        <x-ui.button type="submit" class="rounded-2xl px-10 shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all">Create Account</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
