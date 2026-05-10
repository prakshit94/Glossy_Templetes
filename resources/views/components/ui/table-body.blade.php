@props(['className' => ''])
<tbody {{ $attributes->merge(['class' => '[&_tr:last-child]:border-0 ' . $className]) }}>
    {{ $slot }}
</tbody>
