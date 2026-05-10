@if($teams->hasPages())
    <div class="p-4 border-b border-border/40">
        {{ $teams->links() }}
    </div>
@endif

<x-ui.table>
    <x-ui.table-header>
        <x-ui.table-row>
            <x-ui.table-head class="w-10">
                <input type="checkbox" x-model="allSelected" @change="toggleAll" 
                    class="rounded border-border bg-background text-primary focus:ring-primary/20">
            </x-ui.table-head>
            <x-ui.table-head>Team Name</x-ui.table-head>
            <x-ui.table-head>Owner</x-ui.table-head>
            <x-ui.table-head>Members</x-ui.table-head>
            <x-ui.table-head>Description</x-ui.table-head>
            <x-ui.table-head>Created At</x-ui.table-head>
            <x-ui.table-head className="text-right">Actions</x-ui.table-head>
        </x-ui.table-row>
    </x-ui.table-header>
    <x-ui.table-body>
        @forelse($teams as $team)
        <x-ui.table-row x-bind:class="selectedTeams.includes({{ $team->id }}) ? 'bg-primary/5' : ''">
            <x-ui.table-cell>
                <input type="checkbox" name="team_ids[]" value="{{ $team->id }}" :checked="selectedTeams.includes({{ $team->id }})" @change="toggleTeam({{ $team->id }})"
                    class="rounded border-border bg-background text-primary focus:ring-primary/20">
            </x-ui.table-cell>
            <x-ui.table-cell>
                <div class="flex items-center gap-3">
                    <div class="size-9 rounded-lg bg-orange-500/10 flex items-center justify-center text-orange-600">
                        <x-ui.icon name="users" size="4" />
                    </div>
                    <span class="font-bold text-sm">{{ $team->name }}</span>
                </div>
            </x-ui.table-cell>
            <x-ui.table-cell>
                <div class="flex items-center gap-2">
                    <div class="size-6 rounded-full bg-muted flex items-center justify-center text-[10px] font-bold">
                        {{ substr($team->owner->name, 0, 2) }}
                    </div>
                    <span class="text-xs">{{ $team->owner->name }}</span>
                </div>
            </x-ui.table-cell>
            <x-ui.table-cell>
                <div class="flex -space-x-2">
                    @foreach($team->members->take(3) as $member)
                        <div class="size-7 rounded-full border-2 border-background bg-muted flex items-center justify-center text-[8px] font-bold shadow-sm" title="{{ $member->name }}">
                            {{ substr($member->name, 0, 2) }}
                        </div>
                    @endforeach
                    @if($team->members->count() > 3)
                        <div class="size-7 rounded-full border-2 border-background bg-secondary flex items-center justify-center text-[8px] font-bold text-muted-foreground shadow-sm">
                            +{{ $team->members->count() - 3 }}
                        </div>
                    @endif
                </div>
            </x-ui.table-cell>
            <x-ui.table-cell>
                <p class="text-xs text-muted-foreground line-clamp-1 max-w-[200px]">{{ $team->description ?? 'No description.' }}</p>
            </x-ui.table-cell>
            <x-ui.table-cell>
                <span class="text-xs text-muted-foreground">{{ $team->created_at->format('M d, Y') }}</span>
            </x-ui.table-cell>
            <x-ui.table-cell className="text-right">
                <div class="flex justify-end gap-2">
                    <a href="{{ route('teams.edit', $team) }}">
                        <x-ui.button variant="ghost" size="icon" className="size-8">
                            <x-ui.icon name="edit" size="3" />
                        </x-ui.button>
                    </a>
                    <form action="{{ route('teams.destroy', $team) }}" method="POST" onsubmit="return confirm('Delete team?')">
                        @csrf
                        @method('DELETE')
                        <x-ui.button variant="ghost" size="icon" type="submit" className="size-8 text-destructive">
                            <x-ui.icon name="trash" size="3" />
                        </x-ui.button>
                    </form>
                </div>
            </x-ui.table-cell>
        </x-ui.table-row>
        @empty
        <x-ui.table-row>
            <x-ui.table-cell colspan="6" class="h-24 text-center">
                No teams found.
            </x-ui.table-cell>
        </x-ui.table-row>
        @endforelse
    </x-ui.table-body>
</x-ui.table>

<div class="p-4 border-t border-border/40">
    {{ $teams->links() }}
</div>
