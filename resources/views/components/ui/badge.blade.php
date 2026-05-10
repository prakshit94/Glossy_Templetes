@props([
    'variant' => 'default',
    'className' => '',
])

@php
    $variants = [
        'default' => 'bg-primary text-primary-foreground',
        'secondary' => 'bg-secondary text-secondary-foreground',
        'destructive' => 'bg-destructive text-destructive-foreground',
        'outline' => 'text-foreground border border-input bg-background hover:bg-accent hover:text-accent-foreground',
        'success' => 'bg-emerald-500 text-white',
        'warning' => 'bg-amber-500 text-white',
        'info' => 'bg-blue-500 text-white',
    ];

    $classes = "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 " . $variants[$variant] . " " . $className;
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>
