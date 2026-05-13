@props(['isMobile' => false])

<aside
   class="{{ $isMobile
   ? 'flex flex-col h-full bg-transparent'
   : 'fixed inset-y-0 left-0 z-50 flex flex-col border-r border-sidebar-border/50 bg-sidebar/95 backdrop-blur-2xl transition-all duration-300 ease-[cubic-bezier(0.2,0,0,1)] md:translate-x-0 group/sidebar shadow-2xl shadow-primary/10' }}"
   :class="{
      'w-72': !sidebarCollapsed || {{ $isMobile ? 'true' : 'false' }},
      'w-[4.5rem]': sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }},
      '-translate-x-full': !{{ $isMobile ? 'true' : 'false' }} && !mobileMenuOpen,
      'translate-x-0': {{ $isMobile ? 'true' : 'false' }} || mobileMenuOpen
   }"
   @click.away="mobileMenuOpen = false">
   <div class="h-20 flex items-center px-5 border-b border-sidebar-border/50 relative overflow-hidden shrink-0 group/logo">
      <div class="absolute inset-0 bg-gradient-to-r from-primary/10 via-primary/5 to-transparent opacity-0 group-hover/logo:opacity-100 transition-opacity duration-700"></div>
      <a href="{{ url('/dashboard') }}" class="flex items-center gap-3.5 relative z-10 w-full" :class="sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} ? 'justify-center' : ''">
         <div class="h-10 w-10 min-w-10 rounded-xl bg-gradient-to-br from-primary to-primary/70 flex items-center justify-center shadow-lg shadow-primary/25 shrink-0 transition-all duration-500 group-hover/logo:scale-110 group-hover/logo:rotate-3 ring-1 ring-border/60">
            <x-ui.icon name="sparkles" size="5" class="text-primary-foreground" />
         </div>
         <div class="flex flex-col overflow-hidden transition-all duration-500 ease-out" :class="sidebarCollapsed && !{{ $isMobile ? 'true' : 'false' }} ? 'w-0 opacity-0 absolute translate-x-10' : 'w-auto opacity-100 translate-x-0'">
            <span class="font-heading font-bold text-lg tracking-tight leading-none text-foreground group-hover/logo:text-primary transition-colors duration-300 whitespace-nowrap">
               {{ config('app.name') }}
            </span>
            <span class="text-[10px] font-bold text-muted-foreground uppercase tracking-[0.25em] mt-1 pl-0.5 whitespace-nowrap">
               Command Center
            </span>
         </div>
      </a>
   </div>

   @php
      $filterItems = function (array $items) {
         return array_values(array_filter($items, function ($item) {
            $permission = $item['permission'] ?? null;
            return !$permission || auth()->user()?->can($permission);
         }));
      };

      $accessControlItems = $filterItems([
         ['title' => 'Users', 'url' => '/users', 'active' => request()->is('users*'), 'permission' => 'users.view', 'icon' => '<x-ui.icon name="users" size="4" />'],
         ['title' => 'Teams', 'url' => '/teams', 'active' => request()->is('teams*'), 'permission' => 'teams.view', 'icon' => '<x-ui.icon name="users-2" size="4" />'],
         ['title' => 'Roles', 'url' => '/roles', 'active' => request()->is('roles*'), 'permission' => 'roles.view', 'icon' => '<x-ui.icon name="shield" size="4" />'],
         ['title' => 'Permissions', 'url' => '/permissions', 'active' => request()->is('permissions*'), 'permission' => 'permissions.view', 'icon' => '<x-ui.icon name="lock" size="4" />'],
         ['title' => 'Activity Log', 'url' => '/activities', 'active' => request()->is('activities*'), 'permission' => 'audit.view', 'icon' => '<x-ui.icon name="activity" size="4" />'],
      ]);

      $crmItems = $filterItems([
         ['title' => 'Customers', 'url' => route('customers.index'), 'active' => request()->routeIs('customers.*'), 'permission' => 'customers.view', 'icon' => '<x-ui.icon name="users" size="4" />'],
         ['title' => 'Customer Groups', 'url' => '/customer-groups', 'active' => request()->is('customer-groups*'), 'permission' => 'customer-groups.view', 'icon' => '<x-ui.icon name="users-2" size="4" />'],
         ['title' => 'Reviews & Ratings', 'url' => '/reviews', 'active' => request()->is('reviews*'), 'permission' => 'reviews.view', 'icon' => '<x-ui.icon name="star" size="4" />'],
         ['title' => 'Support Tickets', 'url' => '/support-tickets', 'active' => request()->is('support-tickets*'), 'permission' => 'support-tickets.view', 'icon' => '<x-ui.icon name="mail" size="4" />'],
      ]);

      $catalogItems = $filterItems([
         ['title' => 'Products', 'url' => '/products', 'active' => request()->is('products*'), 'permission' => 'products.view', 'icon' => '<x-ui.icon name="product" size="4" />'],
         ['title' => 'Categories', 'url' => '/categories', 'active' => request()->is('categories*'), 'permission' => 'categories.view', 'icon' => '<x-ui.icon name="category" size="4" />'],
         ['title' => 'Brands', 'url' => '/brands', 'active' => request()->is('brands*'), 'permission' => 'brands.view', 'icon' => '<x-ui.icon name="brand" size="4" />'],
         ['title' => 'Attributes', 'url' => '/attributes', 'active' => request()->is('attributes*'), 'permission' => 'attributes.view', 'icon' => '<x-ui.icon name="list" size="4" />'],
         ['title' => 'Units of Measure', 'url' => '/uoms', 'active' => request()->is('uoms*'), 'permission' => 'uoms.view', 'icon' => '<x-ui.icon name="box" size="4" />'],
         ['title' => 'Tax Rates', 'url' => '/tax-rates', 'active' => request()->is('tax-rates*'), 'permission' => 'tax-rates.view', 'icon' => '<x-ui.icon name="finance" size="4" />'],
         ['title' => 'HSN Codes', 'url' => '/hsn-codes', 'active' => request()->is('hsn-codes*'), 'permission' => 'hsn-codes.view', 'icon' => '<x-ui.icon name="badge-check" size="4" />'],
      ]);

      $inventoryItems = $filterItems([
         ['title' => 'Inventory', 'url' => '/inventory', 'active' => request()->is('inventory*'), 'permission' => 'inventory.view', 'icon' => '<x-ui.icon name="inventory" size="4" />'],
         ['title' => 'Warehouses', 'url' => '/warehouses', 'active' => request()->is('warehouses*'), 'permission' => 'warehouses.view', 'icon' => '<x-ui.icon name="warehouse" size="4" />'],
         ['title' => 'Stock Transfers', 'url' => '/stock-transfers', 'active' => request()->is('stock-transfers*'), 'permission' => 'stock-transfers.view', 'icon' => '<x-ui.icon name="truck-2" size="4" />'],
         ['title' => 'Stock Adjustments', 'url' => '/stock-adjustments', 'active' => request()->is('stock-adjustments*'), 'permission' => 'stock-adjustments.view', 'icon' => '<x-ui.icon name="refresh-cw" size="4" />'],
      ]);

      $salesItems = $filterItems([
         ['title' => 'Orders', 'url' => '/orders', 'active' => request()->is('orders*'), 'permission' => 'orders.view', 'icon' => '<x-ui.icon name="orders" size="4" />'],
         ['title' => 'Invoices', 'url' => '/invoices', 'active' => request()->is('invoices*'), 'permission' => 'invoices.view', 'icon' => '<x-ui.icon name="finance" size="4" />'],
         ['title' => 'Payments', 'url' => '/payments', 'active' => request()->is('payments*'), 'permission' => 'payments.view', 'icon' => '<x-ui.icon name="credit-card" size="4" />'],
         ['title' => 'Order Tracking', 'url' => '/order-tracking', 'active' => request()->is('order-tracking*'), 'permission' => 'order-tracking.view', 'icon' => '<x-ui.icon name="target" size="4" />'],
         ['title' => 'Returns', 'url' => '/returns', 'active' => request()->is('returns*'), 'permission' => 'returns.view', 'icon' => '<x-ui.icon name="return" size="4" />'],
         ['title' => 'Refunds', 'url' => '/refunds', 'active' => request()->is('refunds*'), 'permission' => 'refunds.view', 'icon' => '<x-ui.icon name="refresh-cw" size="4" />'],
         ['title' => 'Replacement', 'url' => '/replacement', 'active' => request()->is('replacement*'), 'permission' => 'replacement.view', 'icon' => '<x-ui.icon name="package" size="4" />'],
      ]);

      $procurementItems = $filterItems([
         ['title' => 'Purchase Orders', 'url' => '/purchase-orders', 'active' => request()->is('purchase-orders*'), 'permission' => 'purchase-orders.view', 'icon' => '<x-ui.icon name="purchase" size="4" />'],
         ['title' => 'Suppliers', 'url' => '/suppliers', 'active' => request()->is('suppliers*'), 'permission' => 'suppliers.view', 'icon' => '<x-ui.icon name="building" size="4" />'],
         ['title' => 'Vendors', 'url' => '/vendors', 'active' => request()->is('vendors*'), 'permission' => 'vendors.view', 'icon' => '<x-ui.icon name="building" size="4" />'],
      ]);

      $operationsItems = $filterItems([
         ['title' => 'Villages', 'url' => '/villages', 'active' => request()->is('villages*'), 'permission' => 'villages.view', 'icon' => '<x-ui.icon name="map-pin" size="4" />'],
         ['title' => 'Services', 'url' => '/services', 'active' => request()->is('services*'), 'permission' => 'services.view', 'icon' => '<x-ui.icon name="box" size="4" />'],
         ['title' => 'Transport', 'url' => '/transport', 'active' => request()->is('transport*'), 'permission' => 'transport.view', 'icon' => '<x-ui.icon name="truck" size="4" />'],
         ['title' => 'Delivery', 'url' => '/delivery', 'active' => request()->is('delivery*'), 'permission' => 'delivery.view', 'icon' => '<x-ui.icon name="truck-2" size="4" />'],
         ['title' => 'Shipment Tracking', 'url' => '/shipment-tracking', 'active' => request()->is('shipment-tracking*'), 'permission' => 'shipment-tracking.view', 'icon' => '<x-ui.icon name="target" size="4" />'],
         ['title' => 'Drivers', 'url' => '/drivers', 'active' => request()->is('drivers*'), 'permission' => 'drivers.view', 'icon' => '<x-ui.icon name="users-2" size="4" />'],
      ]);

      $financeItems = $filterItems([
         ['title' => 'Accounts', 'url' => '/accounts', 'active' => request()->is('accounts*'), 'permission' => 'accounts.view', 'icon' => '<x-ui.icon name="finance" size="4" />'],
         ['title' => 'Expenses', 'url' => '/expenses', 'active' => request()->is('expenses*'), 'permission' => 'expenses.view', 'icon' => '<x-ui.icon name="activity" size="4" />'],
         ['title' => 'Transactions', 'url' => '/transactions', 'active' => request()->is('transactions*'), 'permission' => 'transactions.view', 'icon' => '<x-ui.icon name="credit-card" size="4" />'],
         ['title' => 'Financial Reports', 'url' => '/financial-reports', 'active' => request()->is('financial-reports*'), 'permission' => 'financial-reports.view', 'icon' => '<x-ui.icon name="bar-chart" size="4" />'],
         ['title' => 'Sales Reports', 'url' => '/sales-reports', 'active' => request()->is('sales-reports*'), 'permission' => 'sales-reports.view', 'icon' => '<x-ui.icon name="reports" size="4" />'],
         ['title' => 'Inventory Reports', 'url' => '/inventory-reports', 'active' => request()->is('inventory-reports*'), 'permission' => 'inventory-reports.view', 'icon' => '<x-ui.icon name="inventory" size="4" />'],
         ['title' => 'Customer Analytics', 'url' => '/customer-analytics', 'active' => request()->is('customer-analytics*'), 'permission' => 'customer-analytics.view', 'icon' => '<x-ui.icon name="users" size="4" />'],
         ['title' => 'Performance Reports', 'url' => '/performance-reports', 'active' => request()->is('performance-reports*'), 'permission' => 'performance-reports.view', 'icon' => '<x-ui.icon name="activity" size="4" />'],
      ]);

      $peopleItems = $filterItems([
         ['title' => 'Employees', 'url' => '/employees', 'active' => request()->is('employees*'), 'permission' => 'employees.view', 'icon' => '<x-ui.icon name="employees" size="4" />'],
         ['title' => 'Attendance', 'url' => '/attendance', 'active' => request()->is('attendance*'), 'permission' => 'attendance.view', 'icon' => '<x-ui.icon name="calendar" size="4" />'],
         ['title' => 'Payroll', 'url' => '/payroll', 'active' => request()->is('payroll*'), 'permission' => 'payroll.view', 'icon' => '<x-ui.icon name="finance" size="4" />'],
         ['title' => 'Departments', 'url' => '/departments', 'active' => request()->is('departments*'), 'permission' => 'departments.view', 'icon' => '<x-ui.icon name="building" size="4" />'],
      ]);

      $marketingItems = $filterItems([
         ['title' => 'Campaigns', 'url' => '/campaigns', 'active' => request()->is('campaigns*'), 'permission' => 'campaigns.view', 'icon' => '<x-ui.icon name="marketing" size="4" />'],
         ['title' => 'Coupons', 'url' => '/coupons', 'active' => request()->is('coupons*'), 'permission' => 'coupons.view', 'icon' => '<x-ui.icon name="gift" size="4" />'],
         ['title' => 'Email Marketing', 'url' => '/email-marketing', 'active' => request()->is('email-marketing*'), 'permission' => 'email-marketing.view', 'icon' => '<x-ui.icon name="mail" size="4" />'],
      ]);
   @endphp

   <div class="flex-1 overflow-y-auto custom-scrollbar py-6 px-3 space-y-8">
      <div class="space-y-1">
         <div class="px-3 mb-2 transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 h-0 hidden' : 'opacity-100'">
            <h3 class="text-[10px] font-extrabold uppercase tracking-widest text-muted-foreground/60">Overview</h3>
         </div>
         <x-layout.nav-link title="Dashboard" url="/dashboard" :active="request()->is('dashboard')">
            <x-slot name="icon"><x-ui.icon name="dashboard" size="5" /></x-slot>
         </x-layout.nav-link>
      </div>

      @if(count($accessControlItems) > 0 || count($crmItems) > 0)
      <div class="space-y-1">
         <div class="px-3 mb-2 transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 h-0 hidden' : 'opacity-100'">
            <h3 class="text-[10px] font-extrabold uppercase tracking-widest text-muted-foreground/60">Access & People</h3>
         </div>
         @if(count($accessControlItems) > 0)
         <x-layout.nav-collapsible title="Access Controller" :active="request()->is('users*') || request()->is('teams*') || request()->is('roles*') || request()->is('permissions*') || request()->is('activities*')" :items="$accessControlItems">
            <x-slot name="icon"><x-ui.icon name="shield-check" size="5" /></x-slot>
         </x-layout.nav-collapsible>
         @endif
         @if(count($crmItems) > 0)
         <x-layout.nav-collapsible title="Customers & CRM" :active="request()->is('customers*') || request()->is('customer-groups*') || request()->is('reviews*') || request()->is('support-tickets*')" :items="$crmItems">
            <x-slot name="icon"><x-ui.icon name="users" size="5" /></x-slot>
         </x-layout.nav-collapsible>
         @endif
      </div>
      @endif

      <div class="space-y-1">
         <div class="px-3 mb-2 transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 h-0 hidden' : 'opacity-100'">
            <h3 class="text-[10px] font-extrabold uppercase tracking-widest text-muted-foreground/60">Core Operations</h3>
         </div>
         <x-layout.nav-collapsible title="Catalog" :active="request()->is('products*') || request()->is('categories*') || request()->is('brands*') || request()->is('attributes*') || request()->is('uoms*') || request()->is('tax-rates*') || request()->is('hsn-codes*')" :items="$catalogItems">
            <x-slot name="icon"><x-ui.icon name="product" size="5" /></x-slot>
         </x-layout.nav-collapsible>
         <x-layout.nav-collapsible title="Inventory" :active="request()->is('inventory*') || request()->is('warehouses*') || request()->is('stock-transfers*') || request()->is('stock-adjustments*')" :items="$inventoryItems">
            <x-slot name="icon"><x-ui.icon name="warehouse" size="5" /></x-slot>
         </x-layout.nav-collapsible>
         <x-layout.nav-collapsible title="Sales Flow" :active="request()->is('orders*') || request()->is('invoices*') || request()->is('payments*') || request()->is('order-tracking*') || request()->is('returns*') || request()->is('refunds*') || request()->is('replacement*')" :items="$salesItems">
            <x-slot name="icon"><x-ui.icon name="shopping-bag" size="5" /></x-slot>
         </x-layout.nav-collapsible>
         <x-layout.nav-collapsible title="Procurement" :active="request()->is('purchase-orders*') || request()->is('suppliers*') || request()->is('vendors*')" :items="$procurementItems">
            <x-slot name="icon"><x-ui.icon name="purchase" size="5" /></x-slot>
         </x-layout.nav-collapsible>
      </div>

      @if(count($operationsItems) > 0 || count($financeItems) > 0)
      <div class="space-y-1">
         <div class="px-3 mb-2 transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 h-0 hidden' : 'opacity-100'">
            <h3 class="text-[10px] font-extrabold uppercase tracking-widest text-muted-foreground/60">Operations & Insights</h3>
         </div>
         @if(count($operationsItems) > 0)
         <x-layout.nav-collapsible title="Field Operations" :active="request()->is('villages*') || request()->is('services*') || request()->is('transport*') || request()->is('delivery*') || request()->is('shipment-tracking*') || request()->is('drivers*')" :items="$operationsItems">
            <x-slot name="icon"><x-ui.icon name="truck" size="5" /></x-slot>
         </x-layout.nav-collapsible>
         @endif
         @if(count($financeItems) > 0)
         <x-layout.nav-collapsible title="Finance & Analytics" :active="request()->is('accounts*') || request()->is('expenses*') || request()->is('transactions*') || request()->is('financial-reports*') || request()->is('sales-reports*') || request()->is('inventory-reports*') || request()->is('customer-analytics*') || request()->is('performance-reports*')" :items="$financeItems">
            <x-slot name="icon"><x-ui.icon name="bar-chart" size="5" /></x-slot>
         </x-layout.nav-collapsible>
         @endif
      </div>
      @endif

      @if(count($peopleItems) > 0 || count($marketingItems) > 0)
      <div class="space-y-1">
         <div class="px-3 mb-2 transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 h-0 hidden' : 'opacity-100'">
            <h3 class="text-[10px] font-extrabold uppercase tracking-widest text-muted-foreground/60">Growth & Workforce</h3>
         </div>
         @if(count($peopleItems) > 0)
         <x-layout.nav-collapsible title="Human Resources" :active="request()->is('employees*') || request()->is('attendance*') || request()->is('payroll*') || request()->is('departments*')" :items="$peopleItems">
            <x-slot name="icon"><x-ui.icon name="employees" size="5" /></x-slot>
         </x-layout.nav-collapsible>
         @endif
         @if(count($marketingItems) > 0)
         <x-layout.nav-collapsible title="Marketing" :active="request()->is('campaigns*') || request()->is('coupons*') || request()->is('email-marketing*')" :items="$marketingItems">
            <x-slot name="icon"><x-ui.icon name="marketing" size="5" /></x-slot>
         </x-layout.nav-collapsible>
         @endif
      </div>
      @endif

      @can('settings.view')
      <div class="space-y-1">
         <div class="px-3 mb-2 transition-opacity duration-300" :class="sidebarCollapsed ? 'opacity-0 h-0 hidden' : 'opacity-100'">
            <h3 class="text-[10px] font-extrabold uppercase tracking-widest text-muted-foreground/60">System</h3>
         </div>
         <x-layout.nav-collapsible title="Configuration" :active="request()->is('settings*')" :items="[
            ['title' => 'Settings', 'url' => '/settings', 'active' => request()->is('settings*'), 'icon' => '<x-ui.icon name=\'settings\' size=\'4\' />']
         ]">
            <x-slot name="icon"><x-ui.icon name="settings" size="5" /></x-slot>
         </x-layout.nav-collapsible>
      </div>
      @endcan
   </div>
</aside>
