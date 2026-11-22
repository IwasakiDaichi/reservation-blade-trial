<x-app-layout>
    <x-slot name="header">
        <div>
            <a href="{{ route('admin.venues.index') }}" class="text-sm font-normal text-gray-500 hover:text-gray-800">&larr; All venues</a>
            <h2 class="mt-1 text-xl font-semibold text-gray-800">Add venue</h2>
        </div>
    </x-slot>

    <div class="max-w-3xl rounded-xl bg-white p-6 shadow-sm">
        @include('admin.venues._form', [
            'action' => route('admin.venues.store'),
            'method' => 'POST',
            'submitLabel' => 'Add venue',
        ])
    </div>
</x-app-layout>
