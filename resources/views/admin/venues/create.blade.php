<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('admin.venues.index') }}" class="text-sm font-normal text-[#4A5568] hover:text-[#B21F24]">&larr; All venues</a>
            <h2 class="mt-1 text-xl font-semibold text-[#1A202C]">Add venue</h2>
        </div>
    </x-slot>

    <div class="max-w-3xl border border-[#F1C9CC] bg-white p-5 shadow-sm sm:p-6">
        @include('admin.venues._form', [
            'action' => route('admin.venues.store'),
            'method' => 'POST',
            'submitLabel' => 'Add venue',
        ])
    </div>
</x-app-layout>
