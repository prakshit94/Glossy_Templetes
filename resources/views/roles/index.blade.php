<x-layouts.app pageTitle="Role Management">

    <div
        class="p-6 lg:p-10"
        x-data="roleManager()"
    >

        <!-- Role Stats Widgets -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

            <!-- Total Roles -->
            <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-primary/5 transition-all duration-500 overflow-hidden shadow-2xl">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-primary/10 blur-[50px] rounded-full group-hover:bg-primary/20 transition-all duration-500"></div>

                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-primary/20 to-primary/5 border border-primary/10 text-primary flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="shield" size="7" />
                    </div>

                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">
                            Total Roles
                        </p>

                        <div class="text-3xl font-black tracking-tighter text-foreground">
                            {{ $stats['total'] ?? 0 }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Permissions -->
            <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-purple-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-purple-500/10 blur-[50px] rounded-full group-hover:bg-purple-500/20 transition-all duration-500"></div>

                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-purple-500/20 to-purple-500/5 border border-purple-500/10 text-purple-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="key" size="7" />
                    </div>

                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">
                            Total Permissions
                        </p>

                        <div class="text-3xl font-black tracking-tighter text-foreground">
                            {{ $stats['permissions'] ?? 0 }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- New This Month -->
            <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-blue-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-blue-500/10 blur-[50px] rounded-full group-hover:bg-blue-500/20 transition-all duration-500"></div>

                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-blue-500/20 to-blue-500/5 border border-blue-500/10 text-blue-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="plus" size="7" />
                    </div>

                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">
                            New This Month
                        </p>

                        <div class="text-3xl font-black tracking-tighter text-foreground">
                            {{ $stats['newThisMonth'] ?? 0 }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Avg Permissions -->
            <div class="group relative p-6 rounded-3xl bg-card/40 border border-border/60 backdrop-blur-xl hover:bg-orange-500/5 transition-all duration-500 overflow-hidden shadow-2xl">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 size-32 bg-orange-500/10 blur-[50px] rounded-full group-hover:bg-orange-500/20 transition-all duration-500"></div>

                <div class="flex items-center gap-5 relative z-10">
                    <div class="size-14 rounded-2xl bg-gradient-to-tr from-orange-500/20 to-orange-500/5 border border-orange-500/10 text-orange-500 flex items-center justify-center shadow-inner group-hover:scale-110 transition-transform duration-500">
                        <x-ui.icon name="list" size="7" />
                    </div>

                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/60 mb-1">
                            Avg Perms / Role
                        </p>

                        <div class="text-3xl font-black tracking-tighter text-foreground">
                            {{ $stats['avgPermissions'] ?? 0 }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <x-ui.card class="overflow-hidden border-border/60 shadow-2xl bg-card/30 backdrop-blur-2xl rounded-3xl">

            <!-- Header -->
            <x-ui.card-header class="border-b border-border/40 bg-muted/10 p-6">

                <div class="flex flex-col gap-6">

                    <!-- Top Actions -->
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">

                        <!-- Left -->
                        <div class="flex flex-wrap items-center gap-3">

                            <div class="flex bg-muted/50 px-4 py-1.5 rounded-xl border border-border/50 shadow-inner">
                                <span class="text-xs font-bold text-primary tracking-widest uppercase">
                                    System Roles
                                </span>
                            </div>

                            <!-- Bulk Actions -->
                            <div
                                x-show="selectedRoles.length > 0"
                                x-cloak
                                x-transition
                                class="flex items-center gap-2"
                            >

                                <x-ui.dropdown>

                                    <x-slot name="trigger">
                                        <x-ui.button
                                            variant="outline"
                                            size="sm"
                                            class="rounded-xl border-primary/20 bg-primary/5 text-primary font-bold shadow-sm whitespace-nowrap"
                                        >
                                            <span x-text="selectedRoles.length"></span>
                                            Selected

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

                                        <form
                                            action="{{ route('roles.bulk-delete') }}"
                                            method="POST"
                                            onsubmit="return confirm('Delete selected roles?')"
                                        >
                                            @csrf

                                            <input
                                                type="hidden"
                                                name="ids"
                                                :value="JSON.stringify(selectedRoles)"
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

                                                Delete Selected
                                            </button>
                                        </form>

                                    </x-slot>
                                </x-ui.dropdown>
                            </div>
                        </div>

                        <!-- Right -->
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
                                href="{{ route('roles.create') }}"
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

                                    Add Role
                                </x-ui.button>
                            </a>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pt-2">

                        <!-- Per Page -->
                        <div class="flex items-center gap-2">

                            <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-widest hidden sm:inline-block">
                                Show
                            </span>

                            <select
                                x-model="perPage"
                                @change="performSearch()"
                                class="h-9 px-3 py-1.5 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-xs font-medium shadow-sm"
                            >
                                <option value="5">5</option>
                                <option value="10">10</option>
                                <option value="15">15</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
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
                                @input.debounce.500ms="performSearch()"
                                placeholder="Search roles..."
                                class="pl-9 pr-10 py-2 rounded-xl border border-border bg-background/50 focus:bg-background focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all w-full text-xs shadow-sm"
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
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                                    ></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </x-ui.card-header>

            <!-- Table -->
            <x-ui.card-content class="p-0">

                <div
                    id="roles-table-container"
                    class="relative"
                    @click="handlePagination($event)"
                >
                    @include('roles.partials.table')
                </div>

            </x-ui.card-content>

        </x-ui.card>
    </div>

    <!-- Alpine Component -->
    <script>
        function roleManager() {
            return {
                selectedRoles: [],
                allSelected: false,
                search: '',
                perPage: '{{ request('perPage', 10) }}',
                isLoading: false,

                toggleAll() {
                    const checkboxes = document.querySelectorAll(
                        'input[name="role_ids[]"]'
                    );

                    if (this.allSelected) {
                        this.selectedRoles = Array.from(checkboxes).map(
                            el => parseInt(el.value)
                        );
                    } else {
                        this.selectedRoles = [];
                    }
                },

                toggleRole(id) {
                    id = parseInt(id);

                    if (this.selectedRoles.includes(id)) {
                        this.selectedRoles = this.selectedRoles.filter(
                            roleId => roleId !== id
                        );
                    } else {
                        this.selectedRoles.push(id);
                    }
                },

                async fetchTable(url) {

                    if (this.isLoading) return;

                    this.isLoading = true;

                    try {

                        const response = await fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'text/html'
                            }
                        });

                        if (!response.ok) {
                            throw new Error('Failed to fetch data');
                        }

                        const html = await response.text();

                        document.getElementById(
                            'roles-table-container'
                        ).innerHTML = html;

                        this.selectedRoles = [];
                        this.allSelected = false;

                    } catch (error) {

                        console.error(error);

                        alert('Something went wrong while loading data.');

                    } finally {

                        this.isLoading = false;
                    }
                },

                async performSearch() {

                    const params = new URLSearchParams({
                        search: this.search,
                        perPage: this.perPage
                    });

                    const url =
                        `{{ route('roles.index') }}?${params.toString()}`;

                    await this.fetchTable(url);
                },

                async handlePagination(event) {

                    const link = event.target.closest('a');

                    if (
                        !link ||
                        !link.href ||
                        !link.href.includes('page=')
                    ) {
                        return;
                    }

                    event.preventDefault();

                    await this.fetchTable(link.href);
                }
            };
        }
    </script>

</x-layouts.app>