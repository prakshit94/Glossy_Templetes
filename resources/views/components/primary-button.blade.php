<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-primary text-primary-foreground border border-transparent rounded-xl font-bold text-xs uppercase tracking-widest hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary/20 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
