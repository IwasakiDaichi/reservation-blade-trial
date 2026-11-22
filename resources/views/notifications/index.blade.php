<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-slate-800">Notifications</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="space-y-3">
                @forelse ($notifications as $notification)
                    <article class="rounded-lg border {{ $notification->read_at ? 'border-slate-200 bg-white' : 'border-indigo-200 bg-indigo-50' }} p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <h3 class="text-sm font-semibold text-slate-900">{{ $notification->data['title'] ?? 'Notice' }}</h3>
                            <time class="shrink-0 text-xs text-slate-500" datetime="{{ $notification->created_at->toIso8601String() }}">
                                {{ $notification->created_at->diffForHumans() }}
                            </time>
                        </div>
                        <p class="mt-2 text-sm leading-6 text-slate-700">{{ $notification->data['message'] ?? '' }}</p>
                        @if (! empty($notification->data['url']))
                            <a href="{{ $notification->data['url'] }}" class="mt-2 inline-block text-sm font-semibold text-blue-800 underline">Open</a>
                        @endif
                    </article>
                @empty
                    <div class="rounded-lg border border-slate-200 bg-white p-10 text-center text-slate-600">
                        <p class="font-medium text-slate-800">No notifications</p>
                        <p class="mt-1 text-sm">Messages from CPDO about your reservations will appear here.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
