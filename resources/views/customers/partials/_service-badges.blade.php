{{--
    ── Service Availability Badges ──────────────────────────────────────
    Expects: $addrModel  (a PartyAddress instance with village.services
             already eager-loaded via addresses.village.services)

    Displays compact colour-coded pills for every active service that is
    flagged as available (pivot.is_available) at the address's village.
    If the address has no linked village, or no services are available,
    nothing is rendered — so this partial is completely safe to include
    anywhere without side-effects on the surrounding layout.
--}}
@php
    $availableServices = ($addrModel->village && $addrModel->village->relationLoaded('services'))
        ? $addrModel->village->services->filter(
            fn($s) => (bool) $s->pivot->is_available && (bool) $s->is_active
          )
        : collect();

    /* Colour palette — cycles through a set of distinct hues so that
       multiple services look visually distinct from each other.        */
    $palette = [
        ['bg' => 'bg-emerald-500/10',  'text' => 'text-emerald-600',  'border' => 'border-emerald-500/25'],
        ['bg' => 'bg-blue-500/10',     'text' => 'text-blue-600',     'border' => 'border-blue-500/25'],
        ['bg' => 'bg-violet-500/10',   'text' => 'text-violet-600',   'border' => 'border-violet-500/25'],
        ['bg' => 'bg-amber-500/10',    'text' => 'text-amber-600',    'border' => 'border-amber-500/25'],
        ['bg' => 'bg-rose-500/10',     'text' => 'text-rose-600',     'border' => 'border-rose-500/25'],
        ['bg' => 'bg-cyan-500/10',     'text' => 'text-cyan-600',     'border' => 'border-cyan-500/25'],
        ['bg' => 'bg-orange-500/10',   'text' => 'text-orange-600',   'border' => 'border-orange-500/25'],
        ['bg' => 'bg-teal-500/10',     'text' => 'text-teal-600',     'border' => 'border-teal-500/25'],
    ];
@endphp

@if($availableServices->count())
    <div class="mt-3 pt-3 border-t border-border/30">
        <span class="text-[9px] uppercase tracking-widest text-muted-foreground font-black block mb-2">
            Services Available
        </span>
        <div class="flex flex-wrap gap-1.5">
            @foreach($availableServices->values() as $idx => $svc)
                @php $clr = $palette[$idx % count($palette)]; @endphp
                <span
                    class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[9px] font-black uppercase tracking-wide border
                           {{ $clr['bg'] }} {{ $clr['text'] }} {{ $clr['border'] }}"
                    title="{{ $svc->description ?: $svc->name }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2.5"
                         stroke-linecap="round" stroke-linejoin="round"
                         class="size-2.5 shrink-0">
                        <polyline points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                    </svg>
                    {{ $svc->name }}
                </span>
            @endforeach
        </div>
    </div>
@endif
