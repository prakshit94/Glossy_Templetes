<x-layouts.app pageTitle="Partner Performance">
    <div class="p-6 lg:p-10 max-w-[1920px] mx-auto w-full space-y-8">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black tracking-tight">{{ $driver->name }}</h1>
                <p class="text-[11px] font-bold uppercase tracking-widest text-muted-foreground mt-2">LMD partner performance and associated orders</p>
            </div>
            <a href="{{ route('delivery.performance.index') }}"><x-ui.button variant="outline" class="rounded-xl">Back</x-ui.button></a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            @foreach([['Total', $metrics['total']], ['Delivered', $metrics['delivered']], ['Returned', $metrics['returned']], ['Pending', $metrics['pending']], ['Success', $metrics['success_rate'] . '%']] as $metric)
                <x-ui.card class="p-5"><p class="text-[10px] uppercase tracking-widest text-muted-foreground font-black">{{ $metric[0] }}</p><p class="text-2xl font-black mt-1">{{ $metric[1] }}</p></x-ui.card>
            @endforeach
        </div>
        <x-ui.card class="overflow-hidden rounded-3xl">
            @include('delivery-performance.partials.tracking-table', ['trackings' => $trackings])
        </x-ui.card>
    </div>
</x-layouts.app>
