<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center rounded-md border border-transparent bg-[#B21F24] px-4 py-2 font-semibold text-xs uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-[#8A151B] focus:bg-[#8A151B] focus:outline-none focus:ring-2 focus:ring-[#B21F24] focus:ring-offset-2 active:bg-[#741116]']) }}>
    {{ $slot }}
</button>
