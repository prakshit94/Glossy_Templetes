@foreach ($items as $item)
    <a href="{{ $item['url'] ?? '#' }}" 
       class="flex h-9 items-center gap-2.5 rounded-lg px-3 text-sm text-sidebar-foreground hover:bg-primary/5 hover:text-primary transition-all duration-300 {{ ($item['active'] ?? false) ? 'bg-primary/5 font-bold text-primary border border-primary/10' : 'border border-transparent' }}">
        @if($item['icon'] ?? null)
            {!! $item['icon'] !!}
        @endif
        <span class="truncate">{{ $item['title'] }}</span>
    </a>
@endforeach
