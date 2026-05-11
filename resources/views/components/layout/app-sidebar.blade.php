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
               <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="24"
                  height="24"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="1.5"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  class="size-5"
                  >
                  <rect width="7" height="9" x="3" y="3" rx="1" />
                  <rect width="7" height="5" x="14" y="3" rx="1" />
                  <rect width="7" height="9" x="14" y="12" rx="1" />
                  <rect width="7" height="5" x="3" y="16" rx="1" />
               </svg>
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
         'icon' => '
         <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-4">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
         </svg>
         ',
         ],
         [
         'title' => 'Teams',
         'url' => '/teams',
         'active' => request()->is('teams*'),
         'permission' => 'teams.view',
         'icon' => '
         <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-4">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
            <path d="M17 3.13a4 4 0 0 1 0 7.75"/>
         </svg>
         ',
         ],
         [
         'title' => 'Roles',
         'url' => '/roles',
         'active' => request()->is('roles*'),
         'permission' => 'roles.view',
         'icon' => '
         <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-4">
            <rect width="18" height="18" x="3" y="3" rx="2"/>
            <path d="M9 3v18"/>
         </svg>
         ',
         ],
         [
         'title' => 'Permissions',
         'url' => '/permissions',
         'active' => request()->is('permissions*'),
         'permission' => 'permissions.view',
         'icon' => '
         <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-4">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/>
         </svg>
         ',
         ],
         [
         'title' => 'System Activity',
         'url' => '/activities',
         'active' => request()->is('activities*'),
         'permission' => 'audit.view',
         'icon' => '
         <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-4">
            <path d="M12 20h9"/>
            <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>
         </svg>
         ',
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
               <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="24"
                  height="24"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="1.5"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  class="size-5"
                  >
                  <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
                  <path d="M7 11V7a5 5 0 0 1 10 0v4" />
               </svg>
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
         'icon' => '
         <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-4">
            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
            <circle cx="12" cy="10" r="3"/>
         </svg>
         ',
         ],
         [
         'title' => 'Services',
         'url' => '/services',
         'active' => request()->is('services*'),
         'permission' => 'services.view',
         'icon' => '
         <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-4">
            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
            <polyline points="3.29 7 12 12 20.71 7"/>
            <line x1="12" y1="22" x2="12" y2="12"/>
         </svg>
         ',
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
               <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="24"
                  height="24"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="1.5"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  class="size-5"
                  >
                  <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                  <polyline points="3.29 7 12 12 20.71 7"/>
                  <line x1="12" y1="22" x2="12" y2="12"/>
               </svg>
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
               <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7 12 3 4 7m16 0v10l-8 4m8-14-8 4m0 0L4 7m8 4v10"/>
               </svg>
            </x-slot>
         </x-layout.nav-collapsible>
         {{-- Inventory Operations --}}
         <x-layout.nav-collapsible
            title="Inventory Operations"
            :active="
            request()->is('inventory*') ||
            request()->is('warehouses*') ||
            request()->is('stock-transfer*') ||
            request()->is('stock-adjustment*')
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
            'url' => '/stock-transfer',
            'active' => request()->is('stock-transfer*'),
            ],
            [
            'title' => 'Stock Adjustment',
            'url' => '/stock-adjustment',
            'active' => request()->is('stock-adjustment*'),
            ],
            ]"
            >
            <x-slot name="icon">
               <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7l9-4 9 4-9 4-9-4zm0 5l9 4 9-4m-18 5l9 4 9-4"/>
               </svg>
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
               <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-6h13v6M3 5h18v6H3V5z"/>
               </svg>
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
               <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6M5 4h14v16H5z"/>
               </svg>
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
               <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M1 3h15v13H1zm15 4h4l3 3v6h-7"/>
               </svg>
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
               <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-2 0-4 1-4 3s2 3 4 3 4 1 4 3-2 3-4 3"/>
               </svg>
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
    'url' => '/customers',
    'active' => request()->is('customers*'),
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
        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
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
               <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
               </svg>
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
               <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5h18v14H3V5zm0 0 9 7 9-7"/>
               </svg>
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
               <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3v18h18"/>
               </svg>
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
            'icon' => '<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'currentColor\' stroke-width=\'1.5\' stroke-linecap=\'round\' stroke-linejoin=\'round\' class=\'size-4\'><path d=\'M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.72V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.17a2 2 0 0 1 1-1.74l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z\'/><circle cx=\'12\' cy=\'12\' r=\'3\'/></svg>',
            ]
            ]"
            >
            <x-slot name="icon">
               <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="24"
                  height="24"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="1.5"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  class="size-5"
                  >
                  <path
                     d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.72V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.17a2 2 0 0 1 1-1.74l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"
                     />
                  <circle cx="12" cy="12" r="3" />
               </svg>
            </x-slot>
         </x-layout.nav-collapsible>
      </div>
      @endcan
   </div>
</aside>