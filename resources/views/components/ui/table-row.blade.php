@props(['className' => ''])
<tr {{ $attributes->merge(['class' => 'border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted ' . $className]) }}>
    {{ $slot }}
</tr>
