@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex h-16 items-center border-b-2 border-[#B21F24] px-3 text-sm font-semibold leading-5 text-[#B21F24] focus:outline-none focus:border-[#8A151B] transition duration-150 ease-in-out'
            : 'inline-flex h-16 items-center border-b-2 border-transparent px-3 text-sm font-medium leading-5 text-[#1A202C] hover:border-[#F1C9CC] hover:text-[#B21F24] focus:outline-none focus:border-[#B21F24] focus:text-[#B21F24] transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
