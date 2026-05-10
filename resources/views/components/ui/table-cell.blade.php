@props(['className' => ''])
<td {{ $attributes->merge(['class' => 'p-4 align-middle [&:has([role=checkbox])]:pr-0 ' . $className]) }}>
    {{ $slot }}
</td>
