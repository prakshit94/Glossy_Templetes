<x-layouts.app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('User Management') }}
        </h2>
    </x-slot>

    <div class="p-6 lg:p-10" x-data="{ 
        selectedUsers: [], 
        allSelected: false,
        search: '',
        filter: '{{ request('filter', 'active') }}',
        perPage: '{{ request('perPage', 10) }}',
        statusFilter: '{{ request('status', '') }}',
        roleFilter: '{{ request('role', '') }}',
        isLoading: false,

        toggleAll() {
            if (this.allSelected) {
                this.selectedUsers = Array.from(
                    document.querySelectorAll('input[name=\'user_ids[]\']')
                ).map(el => parseInt(el.value));
            } else {
                this.selectedUsers = [];
            }
        },

        toggleUser(id) {
            if (this.selectedUsers.includes(id)) {
                this.selectedUsers = this.selectedUsers.filter(u => u !== id);
            } else {
                this.selectedUsers.push(id);
            }
        },

        async performSearch() {
            this.isLoading = true;

            const res = await fetch(
                `{{ route('users.index') }}?search=${this.search}&filter=${this.filter}&perPage=${this.perPage}&status=${this.statusFilter}&role=${this.roleFilter}`,
                {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }
            );

            const html = await res.text();

            document.getElementById('users-table-container').innerHTML = html;

            this.isLoading = false;
            this.selectedUsers = [];
            this.allSelected = false;
        }
    }">

        <!-- User Stats Widgets -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

            <x-ui.card class="bg-background shadow-sm border-border/40">
                <x-ui.card-content class="p-4 flex items-center gap-4">

                    <div class="size-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                        <x-ui.icon name="users" size="6" />
                    </div>

                    <div>
                        <p class="text-xs font-medium text-muted-foreground mb-1">
                            Total Users
                        </p>

                        <div class="text-2xl font-bold">
                            {{ $stats['total'] ?? 0 }}
                        </div>
                    </div>

                </x-ui.card-content>
            </x-ui.card>

            <x-ui.card class="bg-background shadow-sm border-border/40">
                <x-ui.card-content class="p-4 flex items-center gap-4">

                    <div class="size-12 rounded-xl bg-green-500/10 text-green-500 flex items-center justify-center">
                        <x-ui.icon name="check-circle" size="6" />
                    </div>

                    <div>
                        <p class="text-xs font-medium text-muted-foreground mb-1">
                            Active Users
                        </p>

                        <div class="text-2xl font-bold">
                            {{ $stats['active'] ?? 0 }}
                        </div>
                    </div>

                </x-ui.card-content>
            </x-ui.card>

            <x-ui.card class="bg-background shadow-sm border-border/40">
                <x-ui.card-content class="p-4 flex items-center gap-4">

                    <div class="size-12 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center">
                        <x-ui.icon name="plus" size="6" />
                    </div>

                    <div>
                        <p class="text-xs font-medium text-muted-foreground mb-1">
                            New This Month
                        </p>

                        <div class="text-2xl font-bold">
                            {{ $stats['newThisMonth'] ?? 0 }}
                        </div>
                    </div>

                </x-ui.card-content>
            </x-ui.card>

            <x-ui.card class="bg-background shadow-sm border-border/40">
                <x-ui.card-content class="p-4 flex items-center gap-4">

                    <div class="size-12 rounded-xl bg-orange-500/10 text-orange-500 flex items-center justify-center">
                        <x-ui.icon name="activity" size="6" />
                    </div>

                    <div>
                        <p class="text-xs font-medium text-muted-foreground mb-1">
                            Active Rate
                        </p>

                        <div class="text-2xl font-bold">
                            {{ $stats['activePercentage'] ?? 0 }}%
                        </div>
                    </div>

                </x-ui.card-content>
            </x-ui.card>

        </div>

        <x-ui.card>

            <x-ui.card-header class="border-b border-border/40 bg-muted/10 p-4">

                <div class="flex flex-col gap-4">

                    <!-- Top Row -->
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">

                        <!-- Left Side -->
                        <div class="flex flex-wrap items-center gap-3">

                            <!-- Filters -->
                            <div class="flex bg-muted/50 p-1 rounded-xl border border-border/50 shadow-inner overflow-x-auto max-w-full">

                                <a href="{{ route('users.index') }}"
                                   class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all whitespace-nowrap {{ request('filter') !== 'trashed' ? 'bg-background shadow-sm text-primary ring-1 ring-border/50' : 'text-muted-foreground hover:text-primary' }}">
                                    Active Users ({{ $activeCount ?? 0 }})
                                </a>

                                <a href="{{ route('users.index', ['filter' => 'trashed']) }}"
                                   class="px-4 py-1.5 rounded-lg text-xs font-bold transition-all whitespace-nowrap {{ request('filter') === 'trashed' ? 'bg-background shadow-sm text-destructive ring-1 ring-border/50' : 'text-muted-foreground hover:text-destructive' }}">
                                    Deleted ({{ $trashedCount ?? 0 }})
                                </a>

                            </div>

                            <!-- Bulk Actions -->
                            <div
                                x-show="selectedUsers.length > 0"
                                x-cloak
                                x-transition
                                class="flex items-center gap-2 animate-in fade-in slide-in-from-left-4 duration-300"
                            >

                                <x-ui.dropdown>

                                    <x-slot name="trigger">

                                        <x-ui.button
                                            variant="outline"
                                            size="sm"
                                            class="rounded-xl border-primary/20 bg-primary/5 text-primary font-bold shadow-sm whitespace-nowrap"
                                        >
                                            <span x-text="selectedUsers.length"></span> Selected

                                            <x-ui.icon
                                                name="chevron-down"
                                                size="3"
                                                class="ml-2"
                                            />

                                        </x-ui.button>

                                    </x-slot>

                                    <x-slot name="content">

                                        <x-ui.dropdown-label>
                                            Bulk Actions
                                        </x-ui.dropdown-label>

                                        <template x-if="filter !== 'trashed'">

                                            <div>

                                                <form action="{{ route('users.bulk-status') }}" method="POST">
                                                    @csrf

                                                    <input
                                                        type="hidden"
                                                        name="ids"
                                                        :value="JSON.stringify(selectedUsers)"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="status"
                                                        value="active"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="w-full text-left px-2 py-1.5 text-xs hover:bg-muted rounded-md flex items-center"
                                                    >
                                                        <x-ui.icon
                                                            name="check-circle"
                                                            size="3"
                                                            class="mr-2 text-green-500"
                                                        />

                                                        Activate
                                                    </button>

                                                </form>

                                                <form action="{{ route('users.bulk-status') }}" method="POST">
                                                    @csrf

                                                    <input
                                                        type="hidden"
                                                        name="ids"
                                                        :value="JSON.stringify(selectedUsers)"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="status"
                                                        value="suspended"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="w-full text-left px-2 py-1.5 text-xs hover:bg-muted rounded-md flex items-center"
                                                    >
                                                        <x-ui.icon
                                                            name="slash"
                                                            size="3"
                                                            class="mr-2 text-orange-500"
                                                        />

                                                        Suspend
                                                    </button>

                                                </form>

                                                <x-ui.separator class="my-1 opacity-50" />

                                                <form
                                                    action="{{ route('users.bulk-delete') }}"
                                                    method="POST"
                                                    onsubmit="return confirm('Move selected users to trash?')"
                                                >
                                                    @csrf

                                                    <input
                                                        type="hidden"
                                                        name="ids"
                                                        :value="JSON.stringify(selectedUsers)"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="w-full text-left px-2 py-1.5 text-xs hover:bg-muted rounded-md flex items-center text-destructive"
                                                    >
                                                        <x-ui.icon
                                                            name="trash"
                                                            size="3"
                                                            class="mr-2"
                                                        />

                                                        Move to Trash
                                                    </button>

                                                </form>

                                            </div>

                                        </template>

                                        <template x-if="filter === 'trashed'">

                                            <div>

                                                <form action="{{ route('users.bulk-restore') }}" method="POST">
                                                    @csrf

                                                    <input
                                                        type="hidden"
                                                        name="ids"
                                                        :value="JSON.stringify(selectedUsers)"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="w-full text-left px-2 py-1.5 text-xs hover:bg-muted rounded-md flex items-center text-green-600"
                                                    >
                                                        <x-ui.icon
                                                            name="refresh-cw"
                                                            size="3"
                                                            class="mr-2"
                                                        />

                                                        Restore
                                                    </button>

                                                </form>

                                                <x-ui.separator class="my-1 opacity-50" />

                                                <form
                                                    action="{{ route('users.bulk-force-delete') }}"
                                                    method="POST"
                                                    onsubmit="return confirm('PERMANENTLY delete selected users? This cannot be undone!')"
                                                >
                                                    @csrf

                                                    <input
                                                        type="hidden"
                                                        name="ids"
                                                        :value="JSON.stringify(selectedUsers)"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="w-full text-left px-2 py-1.5 text-xs hover:bg-muted rounded-md flex items-center text-destructive"
                                                    >
                                                        <x-ui.icon
                                                            name="trash-2"
                                                            size="3"
                                                            class="mr-2"
                                                        />

                                                        Permanently Delete
                                                    </button>

                                                </form>

                                            </div>

                                        </template>

                                    </x-slot>

                                </x-ui.dropdown>

                            </div>

                        </div>

                        <!-- Right Side -->
                        <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto">

                            <x-ui.button
                                variant="outline"
                                size="sm"
                                class="flex-1 sm:flex-none rounded-xl font-bold uppercase tracking-widest text-[10px] h-9 shadow-sm"
                                onclick="alert('Import feature coming soon!')"
                            >
                                <x-ui.icon
                                    name="activity"
                                    size="3"
                                    class="mr-2"
                                />

                                Import
                            </x-ui.button>

                            <x-ui.button
                                variant="outline"
                                size="sm"
                                class="flex-1 sm:flex-none rounded-xl font-bold uppercase tracking-widest text-[10px] h-9 shadow-sm"
                                onclick="alert('Export feature coming soon!')"
                            >
                                <x-ui.icon
                                    name="external-link"
                                    size="3"
                                    class="mr-2"
                                />

                                Export
                            </x-ui.button>

                            <a
                                href="{{ route('users.create') }}"
                                class="w-full sm:w-auto mt-2 sm:mt-0"
                            >

                                <x-ui.button
                                    size="sm"
                                    class="w-full rounded-xl font-bold uppercase tracking-widest text-[10px] h-9 shadow-lg shadow-primary/20"
                                >
                                    <x-ui.icon
                                        name="plus"
                                        size="3"
                                        class="mr-2"
                                    />

                                    Add User
                                </x-ui.button>

                            </a>

                        </div>

                    </div>

                    <!-- Bottom Row -->
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pt-2">

                        <!-- Filters -->
                        <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto">

                            <div class="flex items-center gap-2">

                                <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest hidden sm:inline-block">
                                    Show
                                </span>

                                <select
                                    x-model="perPage"
                                    @change="performSearch"
                                    class="h-9 px-3 py-1.5 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-xs font-medium shadow-sm"
                                >
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="15">15</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                </select>

                            </div>

                            <select
                                x-model="statusFilter"
                                @change="performSearch"
                                class="flex-1 sm:flex-none h-9 px-3 py-1.5 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-xs font-medium shadow-sm"
                            >
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="suspended">Suspended</option>
                            </select>

                            <select
                                x-model="roleFilter"
                                @change="performSearch"
                                class="flex-1 sm:flex-none h-9 px-3 py-1.5 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-xs font-medium shadow-sm"
                            >
                                <option value="">All Roles</option>

                                @foreach(\Spatie\Permission\Models\Role::all() as $role)
                                    <option value="{{ $role->name }}">
                                        {{ $role->name }}
                                    </option>
                                @endforeach

                            </select>

                        </div>

                        <!-- Search -->
                        <div class="relative group w-full lg:max-w-xs shrink-0">

                            <x-ui.icon
                                name="search"
                                size="4"
                                class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground group-focus-within:text-primary transition-colors"
                            />

                            <input
                                type="text"
                                x-model="search"
                                @input.debounce.500ms="performSearch"
                                placeholder="Search users..."
                                class="pl-9 pr-4 py-2 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all w-full text-xs shadow-sm"
                            >

                            <div
                                x-show="isLoading"
                                class="absolute right-3 top-1/2 -translate-y-1/2"
                            >
                                <svg
                                    class="animate-spin h-3 w-3 text-primary"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    ></circle>

                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                    ></path>
                                </svg>
                            </div>

                        </div>

                    </div>

                </div>

            </x-ui.card-header>

            <x-ui.card-content class="p-0">

                <div
                    id="users-table-container"
                    class="relative"
                    @click="
                        if ($event.target.closest('a')) {
                            let link = $event.target.closest('a');
                            if (link && link.href && link.href.includes('page=')) {
                                $event.preventDefault();
                                isLoading = true;
                                fetch(link.href, {
                                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                                })
                                .then(res => res.text())
                                .then(html => {
                                    document.getElementById('users-table-container').innerHTML = html;
                                    isLoading = false;
                                });
                            }
                        }
                    "
                >

                    @include('users.partials.table')

                </div>

            </x-ui.card-content>

        </x-ui.card>

    </div>

</x-layouts.app>