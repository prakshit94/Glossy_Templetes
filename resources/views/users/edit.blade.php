<x-layouts.app pageTitle="Edit User: {{ $user->name }}">

    <div class="p-6 lg:p-10">
        <div class="max-w-3xl mx-auto">
            <x-ui.card>
                <form action="{{ route('users.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <x-ui.card-content class="space-y-6 pt-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2 group">
                                <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground group-focus-within:text-primary transition-colors">Full Name</label>
                                <div class="relative">
                                    <x-ui.icon name="user" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required 
                                        class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-background/50 border border-border/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm">
                                </div>
                                @error('name') <p class="text-[10px] font-bold text-destructive uppercase tracking-widest">{{ $message }}</p> @enderror
                            </div>
                            <div class="space-y-2 group">
                                <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground group-focus-within:text-primary transition-colors">Username</label>
                                <div class="relative">
                                    <x-ui.icon name="at-sign" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                    <input type="text" name="username" value="{{ old('username', $user->username) }}" required 
                                        class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-background/50 border border-border/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm">
                                </div>
                                @error('username') <p class="text-[10px] font-bold text-destructive uppercase tracking-widest">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="space-y-2 group">
                            <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground group-focus-within:text-primary transition-colors">Email Address</label>
                            <div class="relative">
                                <x-ui.icon name="mail" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" required 
                                    class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-background/50 border border-border/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm">
                            </div>
                            @error('email') <p class="text-[10px] font-bold text-destructive uppercase tracking-widest">{{ $message }}</p> @enderror
                        </div>

                        <div class="p-5 rounded-2xl bg-muted/20 border border-dashed border-border/60">
                            <p class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground mb-4 flex items-center gap-2">
                                <x-ui.icon name="shield" size="4" />
                                Change Password (Optional)
                            </p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2 group">
                                    <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground group-focus-within:text-primary transition-colors">New Password</label>
                                    <div class="relative">
                                        <x-ui.icon name="lock" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                        <input type="password" name="password" 
                                            class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-background/50 border border-border/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm">
                                    </div>
                                    @error('password') <p class="text-[10px] font-bold text-destructive uppercase tracking-widest">{{ $message }}</p> @enderror
                                </div>
                                <div class="space-y-2 group">
                                    <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground group-focus-within:text-primary transition-colors">Confirm New Password</label>
                                    <div class="relative">
                                        <x-ui.icon name="check-circle" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors" />
                                        <input type="password" name="password_confirmation" 
                                            class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-background/50 border border-border/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2 group">
                                <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground group-focus-within:text-primary transition-colors">Status</label>
                                <div class="relative">
                                    <x-ui.icon name="activity" size="4" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors z-10" />
                                    <select name="status" class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-background/50 border border-border/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all shadow-sm appearance-none cursor-pointer">
                                        <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="suspended" {{ old('status', $user->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
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
                                            <option value="{{ $team->id }}" {{ old('current_team_id', $user->current_team_id) == $team->id ? 'selected' : '' }}>{{ $team->name }}</option>
                                        @endforeach
                                    </select>
                                    <x-ui.icon name="chevron-down" size="4" class="absolute right-4 top-1/2 -translate-y-1/2 text-muted-foreground pointer-events-none" />
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <label class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground">Manage Roles</label>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3 p-4 bg-background/30 rounded-2xl border border-border/40 shadow-inner">
                                @foreach($roles as $role)
                                <label class="flex items-center gap-3 cursor-pointer group p-2 rounded-xl hover:bg-background/80 transition-all border border-transparent hover:border-border/50 hover:shadow-sm">
                                    <input type="checkbox" name="roles[]" value="{{ $role->name }}" 
                                        {{ in_array($role->name, $userRoles) ? 'checked' : '' }}
                                        class="rounded-md border-border bg-background text-primary focus:ring-primary/20 size-4">
                                    <span class="text-xs font-bold text-muted-foreground group-hover:text-primary transition-colors">{{ $role->name }}</span>
                                </label>
                                @endforeach
                            </div>
                            @error('roles') <p class="text-[10px] font-bold text-destructive uppercase tracking-widest">{{ $message }}</p> @enderror
                        </div>

                        <div class="p-5 rounded-2xl bg-muted/20 border border-border/60 space-y-4">
                            <p class="text-xs font-extrabold uppercase tracking-widest text-muted-foreground flex items-center gap-2">
                                <x-ui.icon name="shield-check" size="4" />
                                Security & Devices
                            </p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="p-3 rounded-xl bg-background border border-border/50 shadow-sm">
                                    <p class="text-[10px] font-extrabold uppercase tracking-widest text-muted-foreground mb-1">Last Login</p>
                                    <p class="text-xs font-bold">{{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</p>
                                    <p class="text-[9px] font-mono text-muted-foreground/60">{{ $user->last_login_ip ?? 'No IP recorded' }}</p>
                                </div>
                                
                                <div class="p-3 rounded-xl bg-background border border-border/50 shadow-sm">
                                    <p class="text-[10px] font-extrabold uppercase tracking-widest text-muted-foreground mb-1">Trusted Devices</p>
                                    <div class="flex items-center gap-2">
                                        <x-ui.badge variant="outline" className="text-xs font-bold bg-primary/5 border-primary/20 text-primary">
                                            {{ $user->trusted_devices_count }}
                                        </x-ui.badge>
                                        <span class="text-[10px] text-muted-foreground">Registered</span>
                                    </div>
                                </div>

                                <div class="p-3 rounded-xl bg-background border border-border/50 shadow-sm">
                                    <p class="text-[10px] font-extrabold uppercase tracking-widest text-muted-foreground mb-1">Passkeys / MFA</p>
                                    <div class="flex items-center gap-2">
                                        <x-ui.badge variant="outline" className="text-xs font-bold bg-green-500/5 border-green-500/20 text-green-600">
                                            {{ $passkeysCount }}
                                        </x-ui.badge>
                                        <span class="text-[10px] text-muted-foreground">Active Keys</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-ui.card-content>
                    <div class="p-6 border-t border-border/40 flex justify-end gap-3">
                        <x-ui.button variant="outline" type="button" onclick="history.back()">Cancel</x-ui.button>
                        <x-ui.button type="submit">Update User</x-ui.button>
                    </div>
                </form>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>