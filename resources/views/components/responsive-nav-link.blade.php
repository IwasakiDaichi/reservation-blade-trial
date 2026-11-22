@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full border-l-4 border-[#B21F24] bg-[#FFF1F2] py-2 ps-3 pe-4 text-start text-base font-semibold text-[#B21F24] focus:border-[#8A151B] focus:bg-[#FFF1F2] focus:text-[#8A151B] transition duration-150 ease-in-out'
            : 'block w-full border-l-4 border-transparent py-2 ps-3 pe-4 text-start text-base font-medium text-[#1A202C] hover:border-[#F1C9CC] hover:bg-[#FFF9F9] hover:text-[#B21F24] focus:border-[#B21F24] focus:bg-[#FFF1F2] focus:text-[#B21F24] transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
