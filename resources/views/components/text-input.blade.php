@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-md border-[#D9A6AA] shadow-sm focus:border-[#B21F24] focus:ring-[#B21F24]']) }}>
