@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-bold text-xs uppercase tracking-wider text-muted-foreground mb-2 px-1']) }}>
    {{ $value ?? $slot }}
</label>
