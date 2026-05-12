@props(['isMobile' => false])

<aside
   class="{{ $isMobile
   ? 'flex flex-col h-full bg-transparent'
   : 'fixed inset-y-0 left-0 z-50 flex flex-col border-r border-sidebar-border/50 bg-sidebar/95 backdrop-blur-2xl transition-all duration-300 ease-[cubic-bezier(0.2,0,0,1)] md:translate-x-0 group/sidebar shadow-2xl shadow-primary/5 dark:shadow-black/50' }}"
   :class="{
      {{-- Width Logic --}}
      'w-72': !sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }},
      'w-[4.5rem]': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }},
      
      {{-- Visibility Logic --}}
      {{-- If not mobile component, handle translation based on mobileMenuOpen state --}}
      '-translate-x-full': !{{ $isMobile ? 'true' : 'false' }} && !mobileMenuOpen,
      'translate-x-0': {{ $isMobile ? 'true' : 'false' }} || mobileMenuOpen
   }"
   @click.away="mobileMenuOpen = false"
   >
   <div
      class="h-20 flex items-center px-5 border-b border-sidebar-border/50 relative overflow-hidden shrink-0 group/logo"
      >
      <div
         class="absolute inset-0 bg-gradient-to-r from-primary/10 via-primary/5 to-transparent opacity-0 group-hover/logo:opacity-100 transition-opacity duration-700"
         ></div>
      <a
         href="{{ url('/dashboard') }}"
         class="flex items-center gap-3.5 relative z-10 w-full"
         :class="sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} ? 'justify-center' : ''"
         >
         <div
            class="h-10 w-10 min-w-10 rounded-xl bg-gradient-to-br from-primary via-indigo-500 to-violet-600 flex items-center justify-center shadow-lg shadow-primary/25 shrink-0 transition-all duration-500 group-hover/logo:scale-110 group-hover/logo:rotate-3 ring-1 ring-white/10"
            >
            <svg
               xmlns="http://www.w3.org/2000/svg"
               viewBox="0 0 24 24"
               fill="none"
               stroke="currentColor"
               stroke-width="2.5"
               stroke-linecap="round"
               stroke-linejoin="round"
               class="w-5 h-5 text-white drop-shadow-md"
               >
               <path
                  d="M15 6v12a3 3 0 1 0 3-3H6a3 3 0 1 0 3 3V6a3 3 0 1 0-3 3h12a3 3 0 1 0-3-3"
                  />
            </svg>
         </div>
         <div
            class="flex flex-col overflow-hidden transition-all duration-500 ease-out"
            :class="sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }}
            ? 'w-0 opacity-0 absolute translate-x-10'
            : 'w-auto opacity-100 translate-x-0'"
            >
            <span
               class="font-heading font-bold text-lg tracking-tight leading-none text-foreground group-hover/logo:text-primary transition-colors duration-300 whitespace-nowrap"
               >
            {{ config('app.name') }}
            </span>
            <span
               class="text-[10px] font-bold text-muted-foreground uppercase tracking-[0.25em] mt-1 pl-0.5 whitespace-nowrap"
               >
            {{
            auth()->check()
            ? 'Welcome, ' . explode(' ', auth()->user()->name)[0]
            : 'Premium Template'
            }}
            </span>
         </div>
      </a>
   </div>
   <div class="flex-1 overflow-y-auto custom-scrollbar py-6 px-3 space-y-8">
      <div class="space-y-1">
         <div
            class="px-3 mb-2 transition-opacity duration-300"
            :class="sidebarCollapsed ? 'opacity-0 h-0 hidden' : 'opacity-100'"
            >
            <h3 class="text-[10px] font-extrabold uppercase tracking-widest text-muted-foreground/60">
               Overview
            </h3>
         </div>
          <x-layout.nav-link
            title="Dashboard"
            url="/dashboard"
            :active="request()->is('dashboard')"
            >
            <x-slot name="icon">
               <x-ui.icon name="dashboard" size="5" />
            </x-slot>
         </x-layout.nav-link>
      </div>
      <div class="space-y-1">
         <div
            class="px-3 mb-2 transition-opacity duration-300"
            :class="sidebarCollapsed ? 'opacity-0 h-0 hidden' : 'opacity-100'"
            >
            <h3 class="text-[10px] font-extrabold uppercase tracking-widest text-muted-foreground/60">
               Management
            </h3>
         </div>
         @php
         $allManagementItems = [
         [
         'title' => 'Users',
         'url' => '/users',
         'active' => request()->is('users*'),
         'permission' => 'users.view',
         'icon' => '<x-ui.icon name="users" size="4" />',
         ],
         [
         'title' => 'Teams',
         'url' => '/teams',
         'active' => request()->is('teams*'),
         'permission' => 'teams.view',
         'icon' => '<x-ui.icon name="users-2" size="4" />',
         ],
         [
         'title' => 'Roles',
         'url' => '/roles',
         'active' => request()->is('roles*'),
         'permission' => 'roles.view',
         'icon' => '<x-ui.icon name="shield" size="4" />',
         ],
         [
         'title' => 'Permissions',
         'url' => '/permissions',
         'active' => request()->is('permissions*'),
         'permission' => 'permissions.view',
         'icon' => '<x-ui.icon name="lock" size="4" />',
         ],
         [
         'title' => 'System Activity',
         'url' => '/activities',
         'active' => request()->is('activities*'),
         'permission' => 'audit.view',
         'icon' => '<x-ui.icon name="activity" size="4" />',
         ],
         ];
         $managementItems = array_filter($allManagementItems, function ($item) {
         return auth()->user()?->can($item['permission']);
         });
         @endphp
         @if(count($managementItems) > 0)
         <x-layout.nav-collapsible
            title="Access Control"
            :active="
            request()->is('users*') ||
            request()->is('teams*') ||
            request()->is('roles*') ||
            request()->is('permissions*') ||
            request()->is('activities*')
            "
            :items="$managementItems"
            >
            <x-slot name="icon">
               <x-ui.icon name="lock" size="5" />
            </x-slot>
         </x-layout.nav-collapsible>
         @endif
      </div>
      <div class="space-y-1">
         <div
            class="px-3 mb-2 transition-opacity duration-300"
            :class="sidebarCollapsed ? 'opacity-0 h-0 hidden' : 'opacity-100'"
            >
            <h3 class="text-[10px] font-extrabold uppercase tracking-widest text-muted-foreground/60">
               Logistics
            </h3>
         </div>
         @php
         $allLogisticsItems = [
         [
         'title' => 'Villages',
         'url' => '/villages',
         'active' => request()->is('villages*'),
         'permission' => 'villages.view',
         'icon' => '<x-ui.icon name="map-pin" size="4" />',
         ],
         [
         'title' => 'Services',
         'url' => '/services',
         'active' => request()->is('services*'),
         'permission' => 'services.view',
         'icon' => '<x-ui.icon name="box" size="4" />',
         ],
         ];
         $logisticsItems = array_filter($allLogisticsItems, function ($item) {
         return auth()->user()?->can($item['permission']);
         });
         @endphp
         @if(count($logisticsItems) > 0)
         <x-layout.nav-collapsible
            title="Logistics"
            :active="
            request()->is('villages*') ||
            request()->is('services*')
            "
            :items="$logisticsItems"
            >
            <x-slot name="icon">
               <x-ui.icon name="box" size="5" />
            </x-slot>
         </x-layout.nav-collapsible>
         @endif
      </div>
      <div class="space-y-1">
         <div
            class="px-3 mb-2 transition-opacity duration-300"
            :class="sidebarCollapsed ? 'opacity-0 h-0 hidden' : 'opacity-100'"
            >
            <h3 class="text-[10px] font-extrabold uppercase tracking-widest text-muted-foreground/60">
               Business Management
            </h3>
         </div>
         {{-- Catalog Management --}}
         <x-layout.nav-collapsible
            title="Catalog Management"
            :active="
            request()->is('products*') ||
            request()->is('categories*') ||
            request()->is('brands*') ||
            request()->is('attributes*')
            "
            :items="[
            [
            'title' => 'Products',
            'url' => '/products',
            'active' => request()->is('products*'),
            ],
            [
            'title' => 'Categories',
            'url' => '/categories',
            'active' => request()->is('categories*'),
            ],
            [
            'title' => 'Brands',
            'url' => '/brands',
            'active' => request()->is('brands*'),
            ],
            [
            'title' => 'Attributes',
            'url' => '/attributes',
            'active' => request()->is('attributes*'),
            ],
            ]"
            >
            <x-slot name="icon">
               <x-ui.icon name="product" size="5" />
            </x-slot>
         </x-layout.nav-collapsible>
         {{-- Inventory Operations --}}
         <x-layout.nav-collapsible
            title="Inventory Operations"
            :active="
            request()->is('inventory*') ||
            request()->is('warehouses*') ||
            request()->is('stock-transfers*') ||
            request()->is('stock-adjustments*')
            "
            :items="[
            [
            'title' => 'Inventory',
            'url' => '/inventory',
            'active' => request()->is('inventory*'),
            ],
            [
            'title' => 'Warehouses',
            'url' => '/warehouses',
            'active' => request()->is('warehouses*'),
            ],
            [
            'title' => 'Stock Transfer',
            'url' => '/stock-transfers',
            'active' => request()->is('stock-transfers*'),
            ],
            [
            'title' => 'Stock Adjustment',
            'url' => '/stock-adjustments',
            'active' => request()->is('stock-adjustments*'),
            ],
            ]"
            >
            <x-slot name="icon">
               <x-ui.icon name="warehouse" size="5" />
            </x-slot>
         </x-layout.nav-collapsible>
         {{-- Sales & Orders --}}
         <x-layout.nav-collapsible
            title="Sales & Orders"
            :active="
            request()->is('orders*') ||
            request()->is('invoices*') ||
            request()->is('payments*') ||
            request()->is('order-tracking*') ||
            request()->is('returns*') ||
            request()->is('refunds*') ||
            request()->is('replacement*')
            "
            :items="[
            [
            'title' => 'Orders',
            'url' => '/orders',
            'active' => request()->is('orders*'),
            ],
            [
            'title' => 'Invoices',
            'url' => '/invoices',
            'active' => request()->is('invoices*'),
            ],
            [
            'title' => 'Payments',
            'url' => '/payments',
            'active' => request()->is('payments*'),
            ],
            [
            'title' => 'Order Tracking',
            'url' => '/order-tracking',
            'active' => request()->is('order-tracking*'),
            ],
            [
            'title' => 'Returns',
            'url' => '/returns',
            'active' => request()->is('returns*'),
            ],
            [
            'title' => 'Refunds',
            'url' => '/refunds',
            'active' => request()->is('refunds*'),
            ],
            [
            'title' => 'Replacement',
            'url' => '/replacement',
            'active' => request()->is('replacement*'),
            ],
            ]"
            >
            <x-slot name="icon">
               <x-ui.icon name="shopping-bag" size="5" />
            </x-slot>
         </x-layout.nav-collapsible>
         {{-- Procurement & Vendors --}}
         <x-layout.nav-collapsible
            title="Procurement & Vendors"
            :active="
            request()->is('purchase-orders*') ||
            request()->is('suppliers*') ||
            request()->is('vendors*')
            "
            :items="[
            [
            'title' => 'Purchase Orders',
            'url' => '/purchase-orders',
            'active' => request()->is('purchase-orders*'),
            ],
            [
            'title' => 'Suppliers',
            'url' => '/suppliers',
            'active' => request()->is('suppliers*'),
            ],
            [
            'title' => 'Vendor Management',
            'url' => '/vendors',
            'active' => request()->is('vendors*'),
            ],
            ]"
            >
            <x-slot name="icon">
               <x-ui.icon name="purchase" size="5" />
            </x-slot>
         </x-layout.nav-collapsible>
         {{-- Logistics & Delivery --}}
         <x-layout.nav-collapsible
            title="Logistics & Delivery"
            :active="
            request()->is('transport*') ||
            request()->is('delivery*') ||
            request()->is('shipment-tracking*') ||
            request()->is('drivers*')
            "
            :items="[
            [
            'title' => 'Transport',
            'url' => '/transport',
            'active' => request()->is('transport*'),
            ],
            [
            'title' => 'Delivery',
            'url' => '/delivery',
            'active' => request()->is('delivery*'),
            ],
            [
            'title' => 'Shipment Tracking',
            'url' => '/shipment-tracking',
            'active' => request()->is('shipment-tracking*'),
            ],
            [
            'title' => 'Drivers',
            'url' => '/drivers',
            'active' => request()->is('drivers*'),
            ],
            ]"
            >
            <x-slot name="icon">
               <x-ui.icon name="truck" size="5" />
            </x-slot>
         </x-layout.nav-collapsible>
         {{-- Finance & Accounting --}}
         <x-layout.nav-collapsible
            title="Finance & Accounting"
            :active="
            request()->is('accounts*') ||
            request()->is('expenses*') ||
            request()->is('transactions*') ||
            request()->is('financial-reports*')
            "
            :items="[
            [
            'title' => 'Accounts',
            'url' => '/accounts',
            'active' => request()->is('accounts*'),
            ],
            [
            'title' => 'Expenses',
            'url' => '/expenses',
            'active' => request()->is('expenses*'),
            ],
            [
            'title' => 'Transactions',
            'url' => '/transactions',
            'active' => request()->is('transactions*'),
            ],
            [
            'title' => 'Financial Reports',
            'url' => '/financial-reports',
            'active' => request()->is('financial-reports*'),
            ],
            ]"
            >
            <x-slot name="icon">
               <x-ui.icon name="credit-card" size="5" />
            </x-slot>
         </x-layout.nav-collapsible>

         {{-- Customer Management --}}
<x-layout.nav-collapsible
    title="Customer Management"
    :active="
    request()->is('customers*') ||
    request()->is('customer-groups*') ||
    request()->is('reviews*') ||
    request()->is('support-tickets*')
    "
    :items="[
    [
    'title' => 'Customers',
    'url' => route('customers.index'),
    'active' => request()->routeIs('customers.*'),
    ],
    [
    'title' => 'Customer Groups',
    'url' => '/customer-groups',
    'active' => request()->is('customer-groups*'),
    ],
    [
    'title' => 'Reviews & Ratings',
    'url' => '/reviews',
    'active' => request()->is('reviews*'),
    ],
    [
    'title' => 'Support Tickets',
    'url' => '/support-tickets',
    'active' => request()->is('support-tickets*'),
    ],
    ]"
    >
    <x-slot name="icon">
        <x-ui.icon name="users" size="5" />
    </x-slot>
</x-layout.nav-collapsible>

         {{-- Human Resources --}}
         <x-layout.nav-collapsible
            title="Human Resources"
            :active="
            request()->is('employees*') ||
            request()->is('attendance*') ||
            request()->is('payroll*') ||
            request()->is('departments*')
            "
            :items="[
            [
            'title' => 'Employees',
            'url' => '/employees',
            'active' => request()->is('employees*'),
            ],
            [
            'title' => 'Attendance',
            'url' => '/attendance',
            'active' => request()->is('attendance*'),
            ],
            [
            'title' => 'Payroll',
            'url' => '/payroll',
            'active' => request()->is('payroll*'),
            ],
            [
            'title' => 'Departments',
            'url' => '/departments',
            'active' => request()->is('departments*'),
            ],
            ]"
            >
            <x-slot name="icon">
               <x-ui.icon name="employees" size="5" />
            </x-slot>
         </x-layout.nav-collapsible>
         {{-- Marketing & CRM --}}
         <x-layout.nav-collapsible
            title="Marketing & CRM"
            :active="
            request()->is('campaigns*') ||
            request()->is('coupons*') ||
            request()->is('email-marketing*')
            "
            :items="[
            [
            'title' => 'Campaigns',
            'url' => '/campaigns',
            'active' => request()->is('campaigns*'),
            ],
            [
            'title' => 'Coupons',
            'url' => '/coupons',
            'active' => request()->is('coupons*'),
            ],
            [
            'title' => 'Email Marketing',
            'url' => '/email-marketing',
            'active' => request()->is('email-marketing*'),
            ],
            ]"
            >
            <x-slot name="icon">
               <x-ui.icon name="mail" size="5" />
            </x-slot>
         </x-layout.nav-collapsible>
         {{-- Analytics & Reports --}}
         <x-layout.nav-collapsible
            title="Analytics & Reports"
            :active="
            request()->is('sales-reports*') ||
            request()->is('inventory-reports*') ||
            request()->is('customer-analytics*') ||
            request()->is('performance-reports*')
            "
            :items="[
            [
            'title' => 'Sales Reports',
            'url' => '/sales-reports',
            'active' => request()->is('sales-reports*'),
            ],
            [
            'title' => 'Inventory Reports',
            'url' => '/inventory-reports',
            'active' => request()->is('inventory-reports*'),
            ],
            [
            'title' => 'Customer Analytics',
            'url' => '/customer-analytics',
            'active' => request()->is('customer-analytics*'),
            ],
            [
            'title' => 'Performance Reports',
            'url' => '/performance-reports',
            'active' => request()->is('performance-reports*'),
            ],
            ]"
            >
            <x-slot name="icon">
               <x-ui.icon name="bar-chart" size="5" />
            </x-slot>
         </x-layout.nav-collapsible>
      </div>
      @can('settings.view')
      <div class="space-y-1">
         <div
            class="px-3 mb-2 transition-opacity duration-300"
            :class="sidebarCollapsed ? 'opacity-0 h-0 hidden' : 'opacity-100'"
            >
            <h3 class="text-[10px] font-extrabold uppercase tracking-widest text-muted-foreground/60">
               System
            </h3>
         </div>
         <x-layout.nav-collapsible
            title="System"
            :active="request()->is('settings*')"
            :items="[
            [
            'title' => 'Settings',
            'url' => '/settings',
            'active' => request()->is('settings*'),
            'icon' => '<x-ui.icon name=\'settings\' size=\'4\' />',
            ]
            ]"
            >
            <x-slot name="icon">
               <x-ui.icon name="settings" size="5" />
            </x-slot>
         </x-layout.nav-collapsible>
      </div>
      @endcan
   </div>
</aside>