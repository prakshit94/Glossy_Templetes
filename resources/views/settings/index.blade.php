<x-layouts.app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('System Settings') }}
        </h2>
    </x-slot>

    <div class="p-6 lg:p-10 space-y-10">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <!-- Left: Info -->
            <div class="space-y-2">
                <h3 class="text-lg font-bold">General Settings</h3>
                <p class="text-sm text-muted-foreground">Configure your core application settings and branding.</p>
            </div>

            <!-- Right: Form -->
            <div class="lg:col-span-2">
                <x-ui.card>
                    <form action="{{ route('settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <x-ui.card-content class="p-6 space-y-6">
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Application Name</label>
                                <input type="text" name="app_name" value="{{ config('app.name') }}" 
                                    class="w-full px-4 py-2 rounded-xl bg-background border border-border focus:ring-2 focus:ring-primary/20 outline-none transition">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Support Email</label>
                                <input type="email" name="support_email" value="support@example.com" 
                                    class="w-full px-4 py-2 rounded-xl bg-background border border-border focus:ring-2 focus:ring-primary/20 outline-none transition">
                            </div>
                            <div class="flex items-center gap-2">
                                <x-ui.checkbox id="maintenance_mode" name="maintenance_mode" />
                                <label for="maintenance_mode" class="text-sm cursor-pointer">Enable Maintenance Mode</label>
                            </div>
                        </x-ui.card-content>
                        <div class="p-6 border-t border-border/40 flex justify-end">
                            <x-ui.button type="submit">Save Changes</x-ui.button>
                        </div>
                    </form>
                </x-ui.card>
            </div>
        </div>

        <x-ui.separator />

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <!-- Left: Info -->
            <div class="space-y-2">
                <h3 class="text-lg font-bold">Security & Maintenance</h3>
                <p class="text-sm text-muted-foreground">Manage system health and security configurations.</p>
            </div>

            <!-- Right: Actions -->
            <div class="lg:col-span-2">
                <x-ui.card>
                    <x-ui.card-content class="p-6 space-y-6">
                        <div class="flex items-center justify-between p-4 rounded-2xl bg-muted/30 border border-border">
                            <div>
                                <p class="text-sm font-bold">System Cache</p>
                                <p class="text-xs text-muted-foreground">Clear application, config, and view cache.</p>
                            </div>
                            <form action="{{ route('settings.clear-cache') }}" method="POST">
                                @csrf
                                <x-ui.button variant="outline" size="sm" type="submit">Clear Cache</x-ui.button>
                            </form>
                        </div>

                        <div class="flex items-center justify-between p-4 rounded-2xl bg-muted/30 border border-border">
                            <div>
                                <p class="text-sm font-bold">Force Logout All Users</p>
                                <p class="text-xs text-muted-foreground">Invalidate all active sessions immediately.</p>
                            </div>
                            <x-ui.button variant="destructive" size="sm">Execute</x-ui.button>
                        </div>
                    </x-ui.card-content>
                </x-ui.card>
            </div>
        </div>
    </div>
</x-layouts.app>
