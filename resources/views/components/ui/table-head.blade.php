@props(['className' => ''])
<th {{ $attributes->merge(['class' => 'h-12 px-4 text-left align-middle font-black uppercase text-[10px] tracking-widest text-muted-foreground/60 [&:has([role=checkbox])]:pr-0 ' . $className]) }}>
    {{ $slot }}
</th>
