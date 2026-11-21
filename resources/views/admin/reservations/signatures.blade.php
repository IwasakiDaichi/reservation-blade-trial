<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Reservation signatures</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl space-y-6 px-4">
            @if (session('success'))
                <div class="rounded border border-green-200 bg-green-50 p-4 text-green-800" role="status">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded border border-red-200 bg-red-50 p-4 text-red-800" role="alert">
                    <ul class="list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="rounded-lg bg-white p-6 shadow-sm">
                <h1 class="text-lg font-bold">Reservation #{{ $reservation->id }}</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Upload staff signatures for the printed form. Applicant signatures are provided by the applicant.
                    Images must be JPG or PNG and no larger than 10 MB.
                </p>

                <form method="POST" enctype="multipart/form-data"
                    action="{{ route('admin.reservations.signatures.update', $reservation) }}"
                    class="mt-6 space-y-5">
                    @csrf
                    @method('PUT')

                    @foreach (\App\Enums\SignatureRole::staff() as $role)
                        @php $field = $role->fileField(); @endphp
                        <div>
                            <label for="{{ $field }}" class="block text-sm font-semibold text-gray-800">
                                {{ $role->label() }}
                                @if ($reservation->signaturePath($role))
                                    <span class="ml-1 font-normal text-green-700">Signature uploaded</span>
                                @else
                                    <span class="ml-1 font-normal text-gray-500">Not uploaded</span>
                                @endif
                            </label>
                            <input id="{{ $field }}" name="{{ $field }}" type="file"
                                accept="image/jpeg,image/png"
                                class="mt-2 block w-full rounded border border-gray-300 px-3 py-2">
                            @error($field)
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach

                    <div class="flex flex-wrap gap-3 pt-2">
                        <button type="submit" class="rounded bg-blue-800 px-5 py-2 font-semibold text-white hover:bg-blue-900">
                            Save signatures
                        </button>
                        <a href="{{ route('admin.reservations.index') }}"
                            class="rounded border border-gray-300 px-5 py-2 font-semibold text-gray-700 hover:bg-gray-50">
                            Back to reservations
                        </a>
                        <a href="{{ route('reservations.print', $reservation) }}" target="_blank" rel="noopener"
                            class="rounded border border-blue-700 px-5 py-2 font-semibold text-blue-800 hover:bg-blue-50">
                            Preview printable form
                        </a>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-app-layout>
