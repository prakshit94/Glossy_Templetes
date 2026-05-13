@if($roles->hasPages())
    <div class="p-4 border-b border-border/40">
        {{ $roles->links() }}
    </div>
@endif

<x-ui.table>
    <x-ui.table-header class="bg-muted/20">
        <x-ui.table-row class="border-b border-border/60">
            <x-ui.table-head class="w-10">
                <input type="checkbox" x-model="allSelected" @change="toggleAll" 
                    class="rounded border-border bg-background text-primary focus:ring-primary/20">
            </x-ui.table-head>
            <x-ui.table-head>Role Name</x-ui.table-head>
            <x-ui.table-head>Permissions</x-ui.table-head>
            <x-ui.table-head>Users</x-ui.table-head>
            <x-ui.table-head className="text-right">Actions</x-ui.table-head>
        </x-ui.table-row>
    </x-ui.table-header>
    <x-ui.table-body>
        @forelse($roles as $role)
        <x-ui.table-row x-bind:class="selectedRoles.includes({{ $role->id }}) ? 'bg-primary/5' : 'hover:bg-muted/10 transition-colors'" class="group">
            <x-ui.table-cell>
                <input type="checkbox" name="role_ids[]" value="{{ $role->id }}" :checked="selectedRoles.includes({{ $role->id }})" @change="toggleRole({{ $role->id }})"
                    class="rounded border-border bg-background text-primary focus:ring-primary/20">
            </x-ui.table-cell>
            <x-ui.table-cell>
                <div class="flex items-center gap-3">
                    <div class="size-8 rounded-xl bg-primary/10 flex items-center justify-center font-bold text-primary group-hover:bg-primary/15 transition-colors">
                        <x-ui.icon name="shield" size="4" />
                    </div>
                    <span class="font-bold">{{ $role->name }}</span>
                </div>
            </x-ui.table-cell>
            <x-ui.table-cell>
                <div class="flex flex-wrap gap-1 max-w-[250px]">
                    @foreach($role->permissions->take(5) as $permission)
                        <x-ui.badge variant="outline" className="text-[9px] px-1 bg-muted/50">{{ $permission->name }}</x-ui.badge>
                    @endforeach
                    @if($role->permissions->count() > 5)
                        <x-ui.badge variant="secondary" className="text-[9px] px-1">+{{ $role->permissions->count() - 5 }} more</x-ui.badge>
                    @endif
                </div>
            </x-ui.table-cell>
            <x-ui.table-cell>
                <span class="font-medium text-xs">{{ $role->users->count() }} Users</span>
            </x-ui.table-cell>
            <x-ui.table-cell className="text-right">
                <div class="flex justify-end gap-2">
                    <a href="{{ route('roles.edit', $role) }}">
                        <x-ui.button variant="ghost" size="icon" className="size-8 rounded-xl hover:bg-primary/10 hover:text-primary transition-colors">
                            <x-ui.icon name="edit" size="3" />
                        </x-ui.button>
                    </a>
                    @if($role->name !== 'Super Admin')
                    <form action="{{ route('roles.destroy', $role) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this role?')">
                        @csrf
                        @method('DELETE')
                        <x-ui.button variant="ghost" size="icon" type="submit" className="size-8 rounded-xl text-destructive hover:bg-destructive/10 transition-colors">
                            <x-ui.icon name="trash" size="3" />
                        </x-ui.button>
                    </form>
                    @endif
                </div>
            </x-ui.table-cell>
        </x-ui.table-row>
        @empty
        <x-ui.table-row>
            <x-ui.table-cell colspan="5" class="h-24 text-center">
                No roles found.
            </x-ui.table-cell>
        </x-ui.table-row>
        @endforelse
    </x-ui.table-body>
</x-ui.table>

@if($roles->hasPages())
    <div class="p-4 border-t border-border/40">
        {{ $roles->links() }}
    </div>
@endif
