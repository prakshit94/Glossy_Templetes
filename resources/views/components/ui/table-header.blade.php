@props(['className' => ''])
<thead {{ $attributes->merge(['class' => '[&_tr]:border-b bg-muted/30 ' . $className]) }}>
    {{ $slot }}
</thead>
