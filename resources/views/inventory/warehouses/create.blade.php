<x-layouts.app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('New Warehouse') }}
        </h2>
    </x-slot>

    <div class="p-6 lg:p-10" x-data="{
        managerSearch: '',
        managerOpen: false,
        managerId: '',
        selectedManagerName: 'Select Manager',
        users: @js($users),

        selectManager(user) {
            this.managerId = user.id;
            this.selectedManagerName = user.name;
            this.managerOpen = false;
            this.managerSearch = user.name;
        },

        getFilteredUsers() {
            const s = this.managerSearch.toLowerCase();
            if (!s) return this.users;
            return this.users.filter(u => u.name.toLowerCase().includes(s) || (u.email && u.email.toLowerCase().includes(s)));
        }
    }">
        <div class="max-w-4xl mx-auto">
            <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">
                <x-ui.card-header class="border-b border-border/40 bg-muted/10 p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="size-12 rounded-2xl bg-primary/10 border border-primary/20 text-primary flex items-center justify-center shadow-inner">
                                <x-ui.icon name="warehouse" size="6" />
                            </div>
                            <div>
                                <h3 class="text-lg font-black text-foreground tracking-tight">Create Warehouse</h3>
                                <p class="text-[10px] uppercase font-bold text-muted-foreground tracking-widest">Register a new storage location</p>
                            </div>
                        </div>
                        <a href="{{ route('warehouses.index') }}">
                            <x-ui.button variant="outline" size="sm" class="rounded-xl font-bold uppercase tracking-widest text-[10px] border-border hover:bg-muted transition-colors">
                                <x-ui.icon name="arrow-left" size="3" class="mr-2" />
                                Back to list
                            </x-ui.button>
                        </a>
                    </div>
                </x-ui.card-header>

                <x-ui.card-content class="p-8">
                    <form action="{{ route('warehouses.store') }}" method="POST" class="space-y-8">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label for="name" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Warehouse Name</label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" required 
                                    class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm font-medium" placeholder="e.g. Central Hub">
                                @error('name') <p class="text-[10px] text-destructive font-bold mt-1 ml-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="space-y-2">
                                <label for="code" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Warehouse Code</label>
                                <input type="text" name="code" id="code" value="{{ old('code') }}" required 
                                    class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm font-medium" placeholder="e.g. WH-001">
                                @error('code') <p class="text-[10px] text-destructive font-bold mt-1 ml-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="location" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Location / Address</label>
                            <input type="text" name="location" id="location" value="{{ old('location') }}" required 
                                class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm font-medium" placeholder="Full address of the warehouse">
                            @error('location') <p class="text-[10px] text-destructive font-bold mt-1 ml-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Manager</label>
                                <div class="relative">
                                    <input type="text" 
                                        placeholder="Search manager..."
                                        x-model="managerSearch"
                                        @focus="managerOpen = true"
                                        @click.away="managerOpen = false"
                                        class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background text-sm font-medium pr-10">
                                    <input type="hidden" name="manager_id" :value="managerId">
                                    <div class="absolute right-4 top-1/2 -translate-y-1/2 text-muted-foreground/40 pointer-events-none">
                                        <x-ui.icon name="search" size="4" />
                                    </div>

                                    <div x-show="managerOpen" 
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-y-1"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        class="absolute z-[100] mt-1 w-full bg-popover border border-border rounded-2xl shadow-2xl p-1 max-h-60 overflow-y-auto custom-scrollbar">
                                        <template x-for="user in getFilteredUsers()" :key="user.id">
                                            <button type="button" 
                                                @click="selectManager(user)"
                                                class="w-full text-left px-4 py-2.5 rounded-xl hover:bg-muted text-sm transition-colors flex flex-col gap-0.5">
                                                <span class="font-bold text-foreground" x-text="user.name"></span>
                                                <span class="text-[10px] text-muted-foreground uppercase" x-text="user.email"></span>
                                            </button>
                                        </template>
                                        <div x-show="getFilteredUsers().length === 0" class="px-4 py-6 text-center text-xs text-muted-foreground italic font-medium">
                                            No users found...
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label for="status" class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/80 ml-1">Status</label>
                                <select name="status" id="status" required 
                                    class="w-full h-12 px-4 rounded-2xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-sm font-black uppercase tracking-widest text-foreground">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4">
                            <x-ui.button type="submit" class="h-14 px-12 rounded-2xl font-black uppercase tracking-[0.2em] text-xs shadow-xl shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all">
                                Create Warehouse
                            </x-ui.button>
                        </div>
                    </form>
                </x-ui.card-content>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
