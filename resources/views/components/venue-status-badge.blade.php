@props(['status'])

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold '.$status->badgeClasses()]) }}>
    {{ $status->label() }}
</span>
