<div x-data="{ open: false }" class="fixed bottom-6 right-6 z-[9999]">
    <!-- Toggle Button -->
    <button @click="open = !open" 
        class="size-14 rounded-full bg-primary text-primary-foreground shadow-2xl flex items-center justify-center hover:scale-110 active:scale-95 transition-all duration-300 group">
        <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-6 transition-transform group-hover:rotate-12"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>
        <svg x-show="open" x-cloak xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-6"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
    </button>

    <!-- Chat Window -->
    <div x-show="open" x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-8 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-8 scale-95"
        class="absolute bottom-20 right-0 w-80 sm:w-96 h-[500px] bg-white dark:bg-zinc-950 border border-border shadow-2xl rounded-[32px] overflow-hidden flex flex-col ring-1 ring-black/5">
        
        <!-- Header -->
        <div class="p-6 bg-primary text-primary-foreground">
            <h3 class="font-bold text-lg">Support Assistant</h3>
            <p class="text-xs opacity-80 mt-1">Ask us anything about the template!</p>
        </div>

        <!-- Messages -->
        <div class="flex-1 overflow-y-auto p-6 space-y-4 bg-secondary/10">
            <div class="flex items-start gap-3">
                <div class="size-8 rounded-full bg-primary flex items-center justify-center text-[10px] font-bold text-primary-foreground">AI</div>
                <div class="bg-white dark:bg-zinc-900 p-3 rounded-2xl rounded-tl-none shadow-sm border border-border text-sm">
                    Hello! How can I help you customize this premium template today?
                </div>
            </div>
        </div>

        <!-- Input -->
        <div class="p-4 bg-white dark:bg-zinc-950 border-t border-border">
            <div class="relative">
                <input type="text" placeholder="Type a message..." 
                    class="w-full pl-4 pr-12 py-3 bg-secondary/30 dark:bg-zinc-900 border-none rounded-2xl focus:ring-2 focus:ring-primary/20 text-sm">
                <button class="absolute right-2 top-1/2 -translate-y-1/2 p-2 text-primary hover:scale-110 transition-transform">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-send-horizontal"><path d="m3 3 3 9-3 9 19-9Z"/><path d="M6 12h16"/></svg>
                </button>
            </div>
        </div>
    </div>
</div>
