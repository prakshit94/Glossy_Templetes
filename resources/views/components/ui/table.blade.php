@props(['className' => ''])
<div class="relative w-full overflow-auto custom-scrollbar">
    <table {{ $attributes->merge(['class' => 'w-full caption-bottom text-sm ' . $className]) }}>
        {{ $slot }}
    </table>
</div>
