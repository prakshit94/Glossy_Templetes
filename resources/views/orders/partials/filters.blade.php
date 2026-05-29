<!-- ========================= -->
<!-- FILTER BAR -->
<!-- ========================= -->

<div class="flex flex-wrap items-center gap-2">

    <!-- ===================================================== -->
    <!-- PRODUCT FILTER -->
    <!-- ===================================================== -->

    @can('orders.filter_product')
    <div class="relative" x-data="{ open: false, filter: '' }">

        <button
            @click="open = !open"
            class="h-9 px-4 flex items-center rounded-xl border border-border bg-background/50 text-[11px] font-bold hover:bg-background transition-all group shadow-sm">

            <span class="text-muted-foreground/80 group-hover:text-blue-500 transition-colors">
                Product
            </span>

            <span class="ml-2 px-1.5 py-0.5 rounded-lg bg-blue-500/10 text-blue-500 font-black text-[10px]">
                <span x-text="productFilter.length"></span> /
                <span x-text="productsList.length"></span>
            </span>

            <x-ui.icon
                name="chevron-down"
                size="3"
                class="ml-2 text-muted-foreground/40" />
        </button>

        <div
            x-show="open"
            x-transition
            @click.away="open = false"
            x-cloak
            class="absolute left-0 mt-2 w-72 bg-popover border border-border rounded-xl shadow-2xl z-[999] p-1">

            <!-- Search -->
            <div class="p-2 border-b border-border bg-muted/10 mb-1">

                <input
                    type="text"
                    x-model="filter"
                    placeholder="Search product name or SKU..."
                    class="w-full px-3 py-1 bg-background rounded-lg border border-border text-[11px] outline-none">

            </div>

            <!-- List -->
            <div class="max-h-60 overflow-y-auto custom-scrollbar">

                <template
                    x-for="item in productsList.filter(i =>
                        (i.name || '').toLowerCase().includes(filter.toLowerCase()) ||
                        ((i.sku || '').toLowerCase().includes(filter.toLowerCase()))
                    )"
                    :key="item.id">

                    <label
                        class="flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-muted cursor-pointer transition-colors"
                        x-bind:class="productFilter.includes(String(item.id)) ? 'bg-blue-500/5' : ''">

                        <input
                            type="checkbox"
                            :value="String(item.id)"
                            x-model="productFilter"
                            @change="performSearch()"
                            class="rounded border-border text-blue-500">

                        <span
                            class="text-[11px] font-bold truncate max-w-[200px]"
                            x-text="item.name + (item.sku ? ' (' + item.sku + ')' : '')">
                        </span>

                    </label>

                </template>

            </div>

        </div>

    </div>
    @endcan

    <!-- ===================================================== -->
    <!-- FULFILLMENT FILTER -->
    <!-- ===================================================== -->

    @can('orders.filter_fulfillment')
    <div class="relative">

        <select
            x-model="fulfillmentFilter"
            @change="performSearch()"
            class="h-9 px-3 rounded-xl border border-border bg-background/50 text-[11px] font-bold text-muted-foreground focus:text-foreground outline-none shadow-sm cursor-pointer hover:bg-background transition-all">

            <option value="">Fulfillment: All Active</option>

            <option value="fulfillable">
                🟢 Fulfillable (In Stock)
            </option>

            <option value="unfulfillable">
                🔴 Unfulfillable (Out of Stock)
            </option>

        </select>

    </div>
    @endcan

    <!-- ===================================================== -->
    <!-- STATUS FILTER -->
    <!-- ===================================================== -->

    @can('orders.filter_status')
    <div class="relative" x-data="{ open: false, filter: '' }">

        <button
            @click="open = !open"
            class="h-9 px-4 flex items-center rounded-xl border border-border bg-background/50 text-[11px] font-bold hover:bg-background transition-all group shadow-sm">

            <span class="text-muted-foreground/80 group-hover:text-primary transition-colors">
                Status
            </span>

            <span class="ml-2 px-1.5 py-0.5 rounded-lg bg-primary/10 text-primary font-black text-[10px]">
                <span x-text="statusFilter.length"></span> /
                <span x-text="statusesList.length"></span>
            </span>

            <x-ui.icon
                name="chevron-down"
                size="3"
                class="ml-2 text-muted-foreground/40" />
        </button>

        <div
            x-show="open"
            x-transition
            @click.away="open = false"
            x-cloak
            class="absolute left-0 mt-2 w-64 bg-popover border border-border rounded-xl shadow-2xl z-[999] p-1">

            <!-- Search -->
            <div class="p-2 border-b border-border bg-muted/10 mb-1">

                <input
                    type="text"
                    x-model="filter"
                    placeholder="Search..."
                    class="w-full px-3 py-1 bg-background rounded-lg border border-border text-[11px] outline-none">

            </div>

            <!-- List -->
            <div class="max-h-60 overflow-y-auto custom-scrollbar">

                <template
                    x-for="item in statusesList.filter(i =>
                        (i || '').toLowerCase().includes(filter.toLowerCase())
                    )"
                    :key="item">

                    <label
                        class="flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-muted cursor-pointer transition-colors"
                        x-bind:class="statusFilter.includes(item) ? 'bg-primary/5' : ''">

                        <input
                            type="checkbox"
                            :value="item"
                            x-model="statusFilter"
                            @change="performSearch()"
                            class="rounded border-border text-primary">

                        <span
                            class="text-[11px] uppercase tracking-widest font-bold"
                            x-text="item.replace(/_/g, ' ')">
                        </span>

                    </label>

                </template>

            </div>

        </div>

    </div>
    @endcan

    <!-- ===================================================== -->
    <!-- STATE FILTER -->
    <!-- ===================================================== -->

    @can('orders.filter_state')
    <div class="relative" x-data="{ open: false, filter: '' }">

        <button
            @click="open = !open"
            class="h-9 px-4 flex items-center rounded-xl border border-border bg-background/50 text-[11px] font-bold hover:bg-background transition-all group shadow-sm">

            <span class="text-muted-foreground/80 group-hover:text-fuchsia-500 transition-colors">
                State
            </span>

            <span class="ml-2 px-1.5 py-0.5 rounded-lg bg-fuchsia-500/10 text-fuchsia-500 font-black text-[10px]">
                <span x-text="stateFilter.length"></span> /
                <span x-text="statesList.length"></span>
            </span>

            <x-ui.icon
                name="chevron-down"
                size="3"
                class="ml-2 text-muted-foreground/40" />
        </button>

        <div
            x-show="open"
            x-transition
            @click.away="open = false"
            x-cloak
            class="absolute left-0 mt-2 w-64 bg-popover border border-border rounded-xl shadow-2xl z-[999] p-1">

            <!-- Search -->
            <div class="p-2 border-b border-border bg-muted/10 mb-1">

                <input
                    type="text"
                    x-model="filter"
                    placeholder="Search..."
                    class="w-full px-3 py-1 bg-background rounded-lg border border-border text-[11px] outline-none">

            </div>

            <!-- List -->
            <div class="max-h-60 overflow-y-auto custom-scrollbar">

                <template
                    x-for="item in statesList.filter(i =>
                        (i || '').toLowerCase().includes(filter.toLowerCase())
                    )"
                    :key="item">

                    <label
                        class="flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-muted cursor-pointer transition-colors"
                        x-bind:class="stateFilter.includes(item) ? 'bg-fuchsia-500/5' : ''">

                        <input
                            type="checkbox"
                            :value="item"
                            x-model="stateFilter"
                            @change="performSearch()"
                            class="rounded border-border text-fuchsia-500">

                        <span
                            class="text-[11px]"
                            x-text="item">
                        </span>

                    </label>

                </template>

            </div>

        </div>

    </div>
    @endcan

    <!-- ===================================================== -->
    <!-- DISTRICT FILTER -->
    <!-- ===================================================== -->

    @can('orders.filter_district')
    <div class="relative" x-data="{ open: false, filter: '' }">

        <button
            @click="open = !open"
            class="h-9 px-4 flex items-center rounded-xl border border-border bg-background/50 text-[11px] font-bold hover:bg-background transition-all group shadow-sm">

            <span class="text-muted-foreground/80 group-hover:text-purple-500 transition-colors">
                District
            </span>

            <span class="ml-2 px-1.5 py-0.5 rounded-lg bg-purple-500/10 text-purple-500 font-black text-[10px]">
                <span x-text="districtFilter.length"></span> /
                <span x-text="districtsList.length"></span>
            </span>

            <x-ui.icon
                name="chevron-down"
                size="3"
                class="ml-2 text-muted-foreground/40" />
        </button>

        <div
            x-show="open"
            x-transition
            @click.away="open = false"
            x-cloak
            class="absolute left-0 mt-2 w-64 bg-popover border border-border rounded-xl shadow-2xl z-[999] p-1">

            <!-- Search -->
            <div class="p-2 border-b border-border bg-muted/10 mb-1">

                <input
                    type="text"
                    x-model="filter"
                    placeholder="Search..."
                    class="w-full px-3 py-1 bg-background rounded-lg border border-border text-[11px] outline-none">

            </div>

            <!-- List -->
            <div class="max-h-60 overflow-y-auto custom-scrollbar">

                <template
                    x-for="item in districtsList.filter(i =>
                        (i || '').toLowerCase().includes(filter.toLowerCase())
                    )"
                    :key="item">

                    <label
                        class="flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-muted cursor-pointer transition-colors"
                        x-bind:class="districtFilter.includes(item) ? 'bg-purple-500/5' : ''">

                        <input
                            type="checkbox"
                            :value="item"
                            x-model="districtFilter"
                            @change="performSearch()"
                            class="rounded border-border text-purple-500">

                        <span
                            class="text-[11px]"
                            x-text="item">
                        </span>

                    </label>

                </template>

            </div>

        </div>

    </div>
    @endcan

    <!-- ===================================================== -->
    <!-- TALUKA FILTER -->
    <!-- ===================================================== -->

    @can('orders.filter_taluka')
    <div class="relative" x-data="{ open: false, filter: '' }">

        <button
            @click="open = !open"
            class="h-9 px-4 flex items-center rounded-xl border border-border bg-background/50 text-[11px] font-bold hover:bg-background transition-all group shadow-sm">

            <span class="text-muted-foreground/80 group-hover:text-indigo-500 transition-colors">
                Taluka
            </span>

            <span class="ml-2 px-1.5 py-0.5 rounded-lg bg-indigo-500/10 text-indigo-500 font-black text-[10px]">
                <span x-text="talukaFilter.length"></span> /
                <span x-text="talukasList.length"></span>
            </span>

            <x-ui.icon
                name="chevron-down"
                size="3"
                class="ml-2 text-muted-foreground/40" />
        </button>

        <div
            x-show="open"
            x-transition
            @click.away="open = false"
            x-cloak
            class="absolute left-0 mt-2 w-64 bg-popover border border-border rounded-xl shadow-2xl z-[999] p-1">

            <!-- Search -->
            <div class="p-2 border-b border-border bg-muted/10 mb-1">

                <input
                    type="text"
                    x-model="filter"
                    placeholder="Search..."
                    class="w-full px-3 py-1 bg-background rounded-lg border border-border text-[11px] outline-none">

            </div>

            <!-- List -->
            <div class="max-h-60 overflow-y-auto custom-scrollbar">

                <template
                    x-for="item in talukasList.filter(i =>
                        (i || '').toLowerCase().includes(filter.toLowerCase())
                    )"
                    :key="item">

                    <label
                        class="flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-muted cursor-pointer transition-colors"
                        x-bind:class="talukaFilter.includes(item) ? 'bg-indigo-500/5' : ''">

                        <input
                            type="checkbox"
                            :value="item"
                            x-model="talukaFilter"
                            @change="performSearch()"
                            class="rounded border-border text-indigo-500">

                        <span
                            class="text-[11px]"
                            x-text="item">
                        </span>

                    </label>

                </template>

            </div>

        </div>

    </div>
    @endcan

    <!-- ===================================================== -->
    <!-- CARRIER FILTER -->
    <!-- ===================================================== -->

    @can('orders.filter_carrier')
    <div class="relative" x-data="{ open: false, filter: '' }">

        <button
            @click="open = !open"
            class="h-9 px-4 flex items-center rounded-xl border border-border bg-background/50 text-[11px] font-bold hover:bg-background transition-all group shadow-sm">

            <span class="text-muted-foreground/80 group-hover:text-cyan-500 transition-colors">
                Carrier
            </span>

            <span class="ml-2 px-1.5 py-0.5 rounded-lg bg-cyan-500/10 text-cyan-500 font-black text-[10px]">
                <span x-text="carrierFilter.length"></span> /
                <span x-text="carriersList.length"></span>
            </span>

            <x-ui.icon
                name="chevron-down"
                size="3"
                class="ml-2 text-muted-foreground/40" />
        </button>

        <div
            x-show="open"
            x-transition
            @click.away="open = false"
            x-cloak
            class="absolute left-0 mt-2 w-64 bg-popover border border-border rounded-xl shadow-2xl z-[999] p-1">

            <!-- Search -->
            <div class="p-2 border-b border-border bg-muted/10 mb-1">

                <input
                    type="text"
                    x-model="filter"
                    placeholder="Search carrier..."
                    class="w-full px-3 py-1 bg-background rounded-lg border border-border text-[11px] outline-none">

            </div>

            <!-- List -->
            <div class="max-h-60 overflow-y-auto custom-scrollbar">

                <template
                    x-for="item in carriersList.filter(i =>
                        (i || '').toLowerCase().includes(filter.toLowerCase())
                    )"
                    :key="item">

                    <label
                        class="flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-muted cursor-pointer transition-colors"
                        x-bind:class="carrierFilter.includes(item) ? 'bg-cyan-500/5' : ''">

                        <input
                            type="checkbox"
                            :value="item"
                            x-model="carrierFilter"
                            @change="performSearch()"
                            class="rounded border-border text-cyan-500">

                        <span
                            class="text-[11px] font-bold truncate max-w-[180px]"
                            x-text="item">
                        </span>

                    </label>

                </template>

                <div x-show="carriersList.length === 0" class="px-3 py-4 text-center text-[10px] text-muted-foreground font-bold uppercase tracking-widest">
                    No carriers available
                </div>

            </div>

        </div>

    </div>
    @endcan

    <!-- ===================================================== -->
    <!-- PLACED DATE SORT -->
    <!-- ===================================================== -->
    @can('orders.filter_sort')
    <div class="relative">
        <select
            x-model="sortDate"
            @change="performSearch()"
            class="h-9 px-3 rounded-xl border border-border bg-background/50 text-[11px] font-bold text-muted-foreground focus:text-foreground outline-none shadow-sm cursor-pointer hover:bg-background transition-all">
            <option value="desc">📅 Latest First</option>
            <option value="asc">📅 Oldest First</option>
        </select>
    </div>
    @endcan

    <!-- ===================================================== -->
    <!-- DATE RANGE FILTER -->
    <!-- ===================================================== -->
    @can('orders.filter_date')
    <div class="flex items-center gap-1.5 h-9 px-3 rounded-xl border border-border bg-background/50 text-[11px] font-bold text-muted-foreground shadow-sm hover:bg-background transition-all">
        <span class="text-muted-foreground/80">From:</span>
        <input 
            type="date" 
            x-model="fromDate" 
            @change="performSearch()" 
            class="bg-transparent border-none text-foreground p-0 text-[11px] font-bold outline-none cursor-pointer w-[100px] focus:ring-0 focus:outline-none">
        <span class="text-muted-foreground/80 ml-1">To:</span>
        <input 
            type="date" 
            x-model="toDate" 
            @change="performSearch()" 
            class="bg-transparent border-none text-foreground p-0 text-[11px] font-bold outline-none cursor-pointer w-[100px] focus:ring-0 focus:outline-none">
        <button 
            type="button" 
            x-show="fromDate || toDate" 
            @click="fromDate = ''; toDate = ''; performSearch()" 
            class="ml-1 text-red-500 hover:text-red-600 transition-colors"
            title="Clear Dates">
            <x-ui.icon name="x-circle" size="3" />
        </button>
    </div>
    @endcan

</div>