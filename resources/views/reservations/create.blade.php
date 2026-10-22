<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">
            New Venue Reservation
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4">
            <div class="bg-white p-6 shadow-sm rounded-lg">

                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                        Please check the form for errors.
                    </div>
                @endif

                <form method="POST" action="{{ route('reservations.store') }}">
                    @csrf

                    <div class="space-y-5">
                        <div>
                            <label class="block font-medium">Department</label>
                            <input name="department" value="{{ old('department') }}"
                                class="w-full rounded border-gray-300" required>
                            @error('department')
                                <p class="text-red-600 text-sm">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block font-medium">Mobile Number</label>
                            <input name="mobile_number" value="{{ old('mobile_number') }}"
                                class="w-full rounded border-gray-300" required>
                            @error('mobile_number')
                                <p class="text-red-600 text-sm">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block font-medium">Purpose of Reservation</label>
                            <textarea name="purpose" class="w-full rounded border-gray-300"
                                required>{{ old('purpose') }}</textarea>
                            @error('purpose')
                                <p class="text-red-600 text-sm">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block font-medium">Venue</label>
                            <select name="venue" class="w-full rounded border-gray-300" required>
                                <option value="">Select a venue</option>
                                @foreach ([
                                    'Campus Gym',
                                    'RCB Dome',
                                    'FGB Hall',
                                    'Centennial Hall',
                                    'AMS Hall',
                                    'Legacy Multipurpose Hall',
                                    'UB Student Center',
                                    'Alumni AVR',
                                    'Centennial AVR',
                                ] as $venue)
                                    <option value="{{ $venue }}"
                                        @selected(old('venue') === $venue)>
                                        {{ $venue }}
                                    </option>
                                @endforeach
                            </select>
                            @error('venue')
                                <p class="text-red-600 text-sm">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block font-medium">Reservation Date</label>
                            <input type="date" name="reserved_date"
                                min="{{ now()->toDateString() }}"
                                value="{{ old('reserved_date') }}"
                                class="w-full rounded border-gray-300" required>
                            @error('reserved_date')
                                <p class="text-red-600 text-sm">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block font-medium">Time / Duration</label>
                            <input name="time" value="{{ old('time') }}"
                                placeholder="e.g. 8:00 AM - 12:00 PM"
                                class="w-full rounded border-gray-300" required>
                            @error('time')
                                <p class="text-red-600 text-sm">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <p class="font-semibold mb-2">Equipment Requested</p>

                            @foreach ([
                                'sound_system' => 'Sound System',
                                'microphone' => 'Microphone',
                                'video_wall' => 'Video Wall',
                                'flags' => 'Flags',
                                'podium' => 'Podium',
                                'platform' => 'Platform',
                            ] as $field => $label)
                                <label class="flex items-center gap-2 mb-2">
                                    <input type="checkbox" name="{{ $field }}" value="1"
                                        @checked(old($field))>
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>

                        <div>
                            <p class="font-semibold mb-2">Physical Setup</p>

                            @foreach ([
                                'tables' => 'Tables',
                                'chairs' => 'Chairs',
                                'removal_of_rings' => 'Removal of Rings',
                            ] as $field => $label)
                                <label class="flex items-center gap-2 mb-2">
                                    <input type="checkbox" name="{{ $field }}" value="1"
                                        @checked(old($field))>
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>

                        <div>
                            <label class="block font-medium">Other Requirements</label>
                            <textarea name="other_requirements"
                                class="w-full rounded border-gray-300">{{ old('other_requirements') }}</textarea>
                        </div>

                        <div>
                            <label class="flex items-start gap-2">
                                <input type="checkbox" name="agreed" value="1"
                                    @checked(old('agreed')) required>
                                <span>I agree to comply with the venue reservation requirements.</span>
                            </label>
                            @error('agreed')
                                <p class="text-red-600 text-sm">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit"
                            class="px-5 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            Submit Reservation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>