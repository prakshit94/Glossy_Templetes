@if($activities->hasPages())
    <div class="p-4 border-b border-border/40">
        {{ $activities->links() }}
    </div>
@endif

<x-ui.table>
    <x-ui.table-header class="bg-muted/20">
        <x-ui.table-row class="border-b border-border/60">
            <x-ui.table-head class="w-12 text-center">
                <input type="checkbox" x-model="allSelected" @change="toggleAll" class="rounded border-border text-primary focus:ring-primary/20 bg-background/50">
            </x-ui.table-head>
            <x-ui.table-head class="w-16">Event</x-ui.table-head>
            <x-ui.table-head>Description</x-ui.table-head>
            <x-ui.table-head>Subject</x-ui.table-head>
            <x-ui.table-head>Causer</x-ui.table-head>
            <x-ui.table-head>Date</x-ui.table-head>
            <x-ui.table-head className="text-right">Attributes (Complete Data)</x-ui.table-head>
        </x-ui.table-row>
    </x-ui.table-header>
    <x-ui.table-body>
        @forelse($activities as $activity)
        <x-ui.table-row class="hover:bg-muted/10 transition-colors">
            <x-ui.table-cell class="text-center">
                <input type="checkbox" name="activity_ids[]" value="{{ $activity->id }}" :checked="selectedActivities.includes({{ $activity->id }})" @change="toggleActivity({{ $activity->id }})" class="rounded border-border text-primary focus:ring-primary/20 bg-background/50">
            </x-ui.table-cell>
            <x-ui.table-cell>
                @php
                    $icon = match($activity->event) {
                        'created' => 'plus-circle',
                        'updated' => 'edit',
                        'deleted' => 'trash-2',
                        'restored' => 'refresh-cw',
                        default => 'info'
                    };
                    $color = match($activity->event) {
                        'created' => 'text-green-500 bg-green-500/10 border-green-500/20',
                        'updated' => 'text-blue-500 bg-blue-500/10 border-blue-500/20',
                        'deleted' => 'text-red-500 bg-red-500/10 border-red-500/20',
                        'restored' => 'text-orange-500 bg-orange-500/10 border-orange-500/20',
                        default => 'text-muted-foreground bg-muted/30 border-border/40'
                    };
                @endphp
                <div class="size-8 rounded-full border flex items-center justify-center {{ $color }}" title="{{ ucfirst($activity->event) }}">
                    <x-ui.icon name="{{ $icon }}" size="4" />
                </div>
            </x-ui.table-cell>
            <x-ui.table-cell>
                <span class="font-bold text-xs">{{ $activity->description }}</span>
            </x-ui.table-cell>
            <x-ui.table-cell>
                @if($activity->subject)
                    <div class="flex flex-col">
                        <span class="text-xs font-bold">{{ class_basename($activity->subject_type) }}</span>
                        <span class="text-[10px] text-muted-foreground">{{ $activity->subject->name ?? 'ID: '.$activity->subject_id }}</span>
                    </div>
                @else
                    <span class="text-xs text-muted-foreground">-</span>
                @endif
            </x-ui.table-cell>
            <x-ui.table-cell>
                @if($activity->causer)
                    <div class="flex items-center gap-2">
                        <div class="size-6 rounded-md bg-primary/10 flex items-center justify-center font-bold text-primary text-[10px]">
                            {{ substr($activity->causer->name, 0, 2) }}
                        </div>
                        <span class="text-xs font-bold">{{ $activity->causer->name }}</span>
                    </div>
                @else
                    <span class="text-xs font-bold text-muted-foreground">System</span>
                @endif
            </x-ui.table-cell>
            <x-ui.table-cell>
                <div class="flex flex-col">
                    <span class="text-xs">{{ $activity->created_at->format('M d, Y') }}</span>
                    <span class="text-[10px] text-muted-foreground">{{ $activity->created_at->format('h:i A') }} ({{ $activity->created_at->diffForHumans() }})</span>
                </div>
            </x-ui.table-cell>
            <x-ui.table-cell className="text-right">
                @if($activity->properties->has('attributes') || $activity->properties->has('old'))
                    <div class="flex flex-col items-end gap-2 max-w-xs ml-auto">
                        @if($activity->properties->has('old'))
                            <div class="w-full text-left bg-red-500/5 border border-red-500/10 rounded-lg p-2 overflow-x-auto text-[10px] text-red-600/80 font-mono">
                                <div class="font-bold text-red-500 mb-1 border-b border-red-500/10 pb-1">OLD DATA</div>
                                <pre class="whitespace-pre-wrap break-all">{{ json_encode($activity->properties['old'], JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        @endif
                        @if($activity->properties->has('attributes'))
                            <div class="w-full text-left bg-green-500/5 border border-green-500/10 rounded-lg p-2 overflow-x-auto text-[10px] text-green-600/80 font-mono">
                                <div class="font-bold text-green-500 mb-1 border-b border-green-500/10 pb-1">NEW DATA</div>
                                <pre class="whitespace-pre-wrap break-all">{{ json_encode($activity->properties['attributes'], JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        @endif
                    </div>
                @else
                    <span class="text-xs text-muted-foreground">-</span>
                @endif
            </x-ui.table-cell>
        </x-ui.table-row>
        @empty
        <x-ui.table-row>
            <x-ui.table-cell colspan="7" class="h-24 text-center text-muted-foreground">
                No activity logs found.
            </x-ui.table-cell>
        </x-ui.table-row>
        @endforelse
    </x-ui.table-body>
</x-ui.table>

@if($activities->hasPages())
    <div class="p-4 border-t border-border/40">
        {{ $activities->links() }}
    </div>
@endif
