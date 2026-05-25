@php
    $records = $records ?? collect();

    $rows = $records instanceof \Illuminate\Pagination\AbstractPaginator
        ? $records->getCollection()
        : $records;
@endphp

@if($records instanceof \Illuminate\Pagination\AbstractPaginator && $records->hasPages())

    <div class="p-4 border-b border-border/40 bg-muted/10 flex justify-end items-center">

        {{ $records->links() }}

    </div>

@endif

<div class="relative">

    <div
        class="pointer-events-none absolute inset-x-8 top-0 h-px bg-gradient-to-r from-transparent via-primary/15 to-transparent hidden sm:block">
    </div>

    <x-ui.table>

        <!-- Header -->
        <x-ui.table-header class="bg-muted/30">

            <x-ui.table-row class="border-b border-border/60">

                <!-- Checkbox -->
                <x-ui.table-head class="w-12 pl-5">

                    <input
                        type="checkbox"
                        x-model="allSelected"
                        @change="toggleAll"
                        class="rounded-md border-border bg-background text-primary focus:ring-primary/25 shadow-sm">

                </x-ui.table-head>

                <!-- Offer -->
                <x-ui.table-head
                    class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap">

                    Offer Identity

                </x-ui.table-head>

                <!-- Type -->
                <x-ui.table-head
                    class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap">

                    Campaign Type

                </x-ui.table-head>

                <!-- Rule -->
                <x-ui.table-head
                    class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap">

                    Discount Logic

                </x-ui.table-head>

                <!-- Schedule -->
                <x-ui.table-head
                    class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap">

                    Active Window

                </x-ui.table-head>

                <!-- Status -->
                <x-ui.table-head
                    class="text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 whitespace-nowrap text-center">

                    Status

                </x-ui.table-head>

                <!-- Actions -->
                <x-ui.table-head
                    class="text-right text-[10px] font-black uppercase tracking-widest text-muted-foreground/70 pr-5">

                    Actions

                </x-ui.table-head>

            </x-ui.table-row>

        </x-ui.table-header>

        <!-- Body -->
        <x-ui.table-body>

            @forelse($rows as $offer)

                @php

                    $isExpired =
                        $offer->ends_at &&
                        \Illuminate\Support\Carbon::parse($offer->ends_at)->isPast();

                    $isActive =
                        $offer->is_active &&
                        !$isExpired;

                    $statusVariant = match (true) {

                        $isExpired => 'warning',

                        $offer->is_active => 'success',

                        default => 'destructive',

                    };

                    $statusLabel = match (true) {

                        $isExpired => 'EXPIRED',

                        $offer->is_active => 'ACTIVE',

                        default => 'INACTIVE',

                    };

                @endphp

                <x-ui.table-row
                    x-bind:class="selectedOffers.includes({{ $offer->id }}) ? 'bg-primary/[0.06] ring-1 ring-inset ring-primary/15 relative z-40' : 'hover:bg-primary/[0.03] hover:z-50 relative'"
                    class="border-b border-border/40 group/row transition-colors duration-200">

                    <!-- Checkbox -->
                    <x-ui.table-cell class="pl-5 align-middle">

                        <input
                            type="checkbox"
                            name="offer_ids[]"
                            value="{{ $offer->id }}"
                            :checked="selectedOffers.includes({{ $offer->id }})"
                            @change="toggleOffer({{ $offer->id }})"
                            class="rounded-md border-border bg-background text-primary focus:ring-primary/25 shadow-sm">

                    </x-ui.table-cell>

                    <!-- Offer Identity -->
                    <x-ui.table-cell class="align-middle">

                        <div class="flex items-center gap-4 py-0.5">

                            <div class="shrink-0">

                                <div
                                    class="size-11 rounded-2xl bg-gradient-to-br from-primary/25 to-primary/5 border border-primary/15 flex items-center justify-center text-primary shadow-inner ring-1 ring-primary/10 group-hover/row:scale-[1.02] transition-transform duration-300">

                                    <x-ui.icon name="tag" size="4.5" />

                                </div>

                            </div>

                            <div class="flex flex-col min-w-0">

                                <div class="flex items-center gap-2">

                                    <span
                                        x-data="{ copied: false }"
                                        @click.prevent.stop="navigator.clipboard.writeText('{{ $offer->name }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                        class="cursor-pointer text-sm font-black tracking-tight text-foreground truncate hover:text-primary transition-colors flex items-center gap-1.5 relative group/copy w-max">

                                        {{ $offer->name }}

                                        <x-ui.icon
                                            name="copy"
                                            size="3"
                                            class="opacity-0 group-hover/copy:opacity-100 transition-opacity text-primary" />

                                        <span
                                            x-show="copied"
                                            x-cloak
                                            class="absolute -top-6 left-0 bg-foreground text-background text-[9px] font-bold px-2 py-0.5 rounded shadow-lg pointer-events-none normal-case tracking-normal">

                                            Copied!

                                        </span>

                                    </span>

                                    <span
                                        class="text-[9px] font-black uppercase px-1.5 py-0.5 rounded bg-muted text-muted-foreground border border-border/40 whitespace-nowrap">

                                        ID: {{ $offer->id }}

                                    </span>

                                </div>

                                <span
                                    class="text-[10px] font-bold text-muted-foreground/65 uppercase tracking-widest">

                                    Priority {{ $offer->priority }}

                                </span>

                            </div>

                        </div>

                    </x-ui.table-cell>

                    <!-- Type -->
                    <x-ui.table-cell class="align-middle">

                        <x-ui.badge
                            :variant="$offer->type === 'order_discount' ? 'default' : 'success'"
                            className="uppercase text-[9px] font-black tracking-[0.2em] px-3 py-1 rounded-xl shadow-sm">

                            {{ $offer->type === 'order_discount'
                                ? 'ORDER DISCOUNT'
                                : 'BOGO'
                            }}

                        </x-ui.badge>

                    </x-ui.table-cell>

                    <!-- Rule -->
                    <x-ui.table-cell class="align-middle">

                        <div class="space-y-1">

                            @if($offer->type === 'order_discount')

                                <span class="text-lg font-black text-foreground">

                                    {{ $offer->discount_type === 'percentage'
                                        ? rtrim(rtrim(number_format((float) $offer->value, 2), '0'), '.') . '%'
                                        : '₹' . number_format((float) $offer->value, 2)
                                    }}

                                </span>

                                <p
                                    class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground">

                                    @if((float) $offer->min_spend > 0)

                                        Min spend ₹{{ number_format((float) $offer->min_spend, 2) }}

                                    @else

                                        No minimum spend

                                    @endif

                                </p>

                                @if((float) $offer->max_discount > 0)

                                    <p
                                        class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground">

                                        Max ₹{{ number_format((float) $offer->max_discount, 2) }}

                                    </p>

                                @endif

                            @else

                                <span class="text-lg font-black text-foreground">

                                    Buy {{ $offer->buy_qty }} Get {{ $offer->get_qty }}

                                </span>

                                <p
                                    class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground">

                                    {{ $offer->product?->name ?? 'No linked product' }}

                                </p>

                            @endif

                        </div>

                    </x-ui.table-cell>

                    <!-- Schedule -->
                    <x-ui.table-cell class="align-middle">

                        <div class="space-y-1">

                            <span class="text-sm font-bold text-foreground">

                                {{ $offer->starts_at
                                    ? $offer->starts_at->format('M d, Y h:i A')
                                    : 'Immediate'
                                }}

                            </span>

                            <p
                                class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground">

                                Until {{ $offer->ends_at
                                    ? $offer->ends_at->format('M d, Y h:i A')
                                    : 'No expiry'
                                }}

                            </p>

                        </div>

                    </x-ui.table-cell>

                    <!-- Status -->
                    <x-ui.table-cell class="align-middle text-center">

                        <x-ui.badge
                            :variant="$statusVariant"
                            className="uppercase text-[9px] font-black tracking-[0.2em] px-3 py-1 rounded-xl shadow-sm">

                            {{ $statusLabel }}

                        </x-ui.badge>

                    </x-ui.table-cell>

                    <!-- Actions -->
                    <x-ui.table-cell class="text-right align-middle pr-5">

                        <div class="flex justify-end gap-1">

                            <!-- Edit -->
                            <a
                                href="{{ route('offers.edit', $offer) }}"
                                title="Edit Offer">

                                <x-ui.button
                                    variant="ghost"
                                    size="icon"
                                    className="size-9 text-muted-foreground hover:text-primary hover:bg-primary/10 rounded-xl border border-transparent hover:border-primary/20 transition-all">

                                    <x-ui.icon name="edit-3" size="4" />

                                </x-ui.button>

                            </a>

                            <!-- Delete -->
                            <form
                                action="{{ route('offers.destroy', $offer) }}"
                                method="POST"
                                class="inline"
                                onsubmit="return confirm('Delete this offer?');">

                                @csrf
                                @method('DELETE')

                                <x-ui.button
                                    type="submit"
                                    variant="ghost"
                                    size="icon"
                                    className="size-9 text-muted-foreground hover:text-destructive hover:bg-destructive/10 rounded-xl border border-transparent hover:border-destructive/20 transition-all">

                                    <x-ui.icon name="trash-2" size="4" />

                                </x-ui.button>

                            </form>

                        </div>

                    </x-ui.table-cell>

                </x-ui.table-row>

            @empty

                <x-ui.table-row>

                    <x-ui.table-cell
                        colspan="7"
                        class="h-72 text-center align-middle p-0">

                        <div
                            class="flex flex-col items-center justify-center gap-5 py-12 px-6">

                            <div
                                class="size-24 rounded-3xl bg-gradient-to-br from-primary/25 via-primary/8 to-transparent border border-primary/20 flex items-center justify-center text-primary shadow-inner ring-1 ring-primary/10">

                                <x-ui.icon
                                    name="tag"
                                    size="12" />

                            </div>

                            <div
                                class="space-y-2 max-w-md text-center">

                                <p
                                    class="text-sm font-black uppercase tracking-[0.2em] text-foreground">

                                    No offers found

                                </p>

                                <p
                                    class="text-[11px] text-muted-foreground font-medium leading-relaxed">

                                    Adjust your filters or create a new offer to start running automated campaigns.

                                </p>

                            </div>

                            <a href="{{ route('offers.create') }}">

                                <x-ui.button
                                    variant="outline"
                                    size="sm"
                                    class="rounded-xl border-border/60 font-bold uppercase tracking-widest text-[10px] h-10 px-6">

                                    Create Offer

                                </x-ui.button>

                            </a>

                        </div>

                    </x-ui.table-cell>

                </x-ui.table-row>

            @endforelse

        </x-ui.table-body>

    </x-ui.table>

</div>

@if($records instanceof \Illuminate\Pagination\AbstractPaginator && $records->hasPages())

    <div
        class="p-4 border-t border-border/40 bg-muted/10 flex justify-end items-center rounded-b-3xl">

        {{ $records->links() }}

    </div>

@endif