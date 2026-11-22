@php
    $currentStatus = old('status', $venue->status?->value ?? 'active');
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6"
    x-data="{ status: @js($currentStatus) }">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    {{-- Name --}}
    <div>
        <x-input-label for="name" value="Venue name" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
            :value="old('name', $venue->name)" maxlength="255" required />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    {{-- Calendar --}}
    <div class="rounded-lg border border-gray-200 p-4"
        x-data="{ useColor: @js((bool) old('use_calendar_color', filled($venue->calendar_color))) }">
        <p class="text-sm font-semibold text-gray-800">Calendar of Activities</p>
        <p class="mt-1 text-xs text-gray-500">How this venue appears on the calendar once a reservation is approved.</p>

        <div class="mt-3 grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="short_name" value="Short name (optional)" />
                <x-text-input id="short_name" name="short_name" type="text" class="mt-1 block w-full"
                    :value="old('short_name', $venue->short_name)" maxlength="50" placeholder="e.g. UB GYM" />
                <x-input-error class="mt-2" :messages="$errors->get('short_name')" />
            </div>

            <div>
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="use_calendar_color" value="1" x-model="useColor" class="rounded border-gray-300">
                    Give this venue a calendar color
                </label>

                <div x-show="useColor" x-cloak class="mt-2 flex items-center gap-3">
                    <input type="color" name="calendar_color" value="{{ old('calendar_color', $venue->calendar_color ?? '#4A86E8') }}"
                        class="h-10 w-14 rounded border border-gray-300">
                    <select name="calendar_text_color" class="rounded-md border-gray-300 text-sm shadow-sm">
                        @php $textColor = old('calendar_text_color', $venue->calendar_text_color ?? '#111827'); @endphp
                        <option value="#111827" @selected($textColor === '#111827')>Dark text</option>
                        <option value="#FFFFFF" @selected($textColor === '#FFFFFF')>White text</option>
                    </select>
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('calendar_color')" />
            </div>
        </div>
    </div>

    {{-- Location --}}
    <div>
        <x-input-label for="location" value="Location (building / floor)" />
        <x-text-input id="location" name="location" type="text" class="mt-1 block w-full"
            :value="old('location', $venue->location)" maxlength="255" />
        <x-input-error class="mt-2" :messages="$errors->get('location')" />
    </div>

    {{-- Capacity --}}
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="capacity_min" value="Minimum capacity (optional)" />
            <x-text-input id="capacity_min" name="capacity_min" type="number" min="1" class="mt-1 block w-full"
                :value="old('capacity_min', $venue->capacity_min)" />
            <p class="mt-1 text-xs text-gray-500">Leave empty if the venue has one fixed capacity.</p>
            <x-input-error class="mt-2" :messages="$errors->get('capacity_min')" />
        </div>

        <div>
            <x-input-label for="capacity_max" value="Maximum capacity" />
            <x-text-input id="capacity_max" name="capacity_max" type="number" min="1" class="mt-1 block w-full"
                :value="old('capacity_max', $venue->capacity_max)" required />
            <x-input-error class="mt-2" :messages="$errors->get('capacity_max')" />
        </div>
    </div>

    {{-- Description --}}
    <div>
        <x-input-label for="description" value="Description" />
        <textarea id="description" name="description" rows="4" maxlength="5000"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#B21F24] focus:ring-[#B21F24]">{{ old('description', $venue->description) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('description')" />
    </div>

    {{-- Amenities --}}
    <div>
        <x-input-label for="amenities" value="Amenities (one per line)" />
        <textarea id="amenities" name="amenities" rows="4" maxlength="3000"
            placeholder="Air conditioning&#10;Projector&#10;Sound system"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#B21F24] focus:ring-[#B21F24]">{{ old('amenities', $venue->amenities) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('amenities')" />
    </div>

    {{-- Rules --}}
    <div>
        <x-input-label for="rules" value="Rules and notes" />
        <textarea id="rules" name="rules" rows="3" maxlength="3000"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#B21F24] focus:ring-[#B21F24]">{{ old('rules', $venue->rules) }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('rules')" />
    </div>

    {{-- Photo --}}
    <div>
        <x-input-label for="image" value="Photo (JPG, PNG or WebP, up to 4 MB)" />

        @if ($venue->imageUrl())
            <div class="mt-2 flex items-center gap-4">
                <img src="{{ $venue->imageUrl() }}" alt="Current photo of {{ $venue->name }}" class="h-20 w-32 rounded-md object-cover">
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="remove_image" value="1" class="rounded border-gray-300">
                    Remove current photo
                </label>
            </div>
        @endif

        <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp"
            class="mt-2 block w-full text-sm text-gray-700">
        <x-input-error class="mt-2" :messages="$errors->get('image')" />
    </div>

    {{-- Status --}}
    <div class="rounded-lg border border-gray-200 p-4">
        <x-input-label for="status" value="Status" />
        <select id="status" name="status" x-model="status"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#B21F24] focus:ring-[#B21F24] sm:w-64">
            @foreach (\App\Enums\VenueStatus::cases() as $case)
                <option value="{{ $case->value }}">{{ $case->label() }}</option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-gray-500">
            Active: can be reserved. Under maintenance: visible to students but cannot be reserved.
            Deactivated: hidden from students.
        </p>
        <x-input-error class="mt-2" :messages="$errors->get('status')" />

        <div x-show="status !== 'active'" x-cloak class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="status_note" value="Reason shown to students (optional)" />
                <x-text-input id="status_note" name="status_note" type="text" class="mt-1 block w-full"
                    :value="old('status_note', $venue->status_note)" maxlength="255" placeholder="e.g. Roof repair" />
                <x-input-error class="mt-2" :messages="$errors->get('status_note')" />
            </div>

            <div>
                <x-input-label for="available_from" value="Expected back on (optional)" />
                <x-text-input id="available_from" name="available_from" type="date" class="mt-1 block w-full"
                    :value="old('available_from', $venue->available_from?->toDateString())" />
                <x-input-error class="mt-2" :messages="$errors->get('available_from')" />
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <x-primary-button>{{ $submitLabel }}</x-primary-button>
        <a href="{{ route('admin.venues.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Cancel</a>
    </div>
</form>
