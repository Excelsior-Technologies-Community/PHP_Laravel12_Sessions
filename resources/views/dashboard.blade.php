<x-app-layout>

    <x-slot name="header">
        <h2 class="font-bold text-xl text-white">
            🔐 Account Security Dashboard
        </h2>
    </x-slot>

    <div class="py-10 bg-gray-950 min-h-screen text-white">

        <div class="max-w-5xl mx-auto px-4">

            {{-- SEARCH --}}
            <form method="GET" class="mb-6">
                <input type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search IP / Browser / Device..."
                    class="w-full px-4 py-3 rounded-xl bg-gray-900 border border-gray-700 text-white focus:ring-2 focus:ring-blue-500">
            </form>

            {{-- STATS --}}
            <div class="grid grid-cols-3 gap-4 mb-6">

                <div class="bg-gray-900 p-5 rounded-2xl border border-gray-800">
                    <p class="text-xs text-gray-400">Total Devices</p>
                    <h3 class="text-2xl font-bold text-blue-400">
                        {{ $sessions->total() }}
                    </h3>
                </div>

                <div class="bg-gray-900 p-5 rounded-2xl border border-gray-800">
                    <p class="text-xs text-gray-400">Active Now</p>
                    <h3 class="text-2xl font-bold text-green-400">1</h3>
                </div>

                <div class="bg-gray-900 p-5 rounded-2xl border border-gray-800">
                    <p class="text-xs text-gray-400">Other Devices</p>
                    <h3 class="text-2xl font-bold text-red-400">
                        {{ max($sessions->total() - 1, 0) }}
                    </h3>
                </div>

            </div>

            {{-- MAIN CARD --}}
            <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">

                <div class="p-5 border-b border-gray-800">
                    <h3 class="font-bold text-lg">Login Activity</h3>
                    <p class="text-xs text-gray-400">Manage your devices</p>
                </div>

                {{-- LIST --}}
                <div class="divide-y divide-gray-800">

                    @forelse($sessions as $session)

                    <div class="p-5 flex justify-between hover:bg-gray-800">

                        <div>

                            <div class="flex items-center gap-2">
                                <span class="text-xl">{{ $session->device_icon }}</span>

                                <p class="font-semibold">
                                    {{ $session->browser }} on {{ $session->platform }}
                                </p>

                                <span class="text-[10px] px-2 py-1 rounded bg-gray-700 text-white">
                                    {{ $session->device_type }}
                                </span>

                                @if($session->is_current_device)
                                <span class="text-[10px] px-2 py-1 rounded bg-green-600 text-white">
                                    CURRENT
                                </span>
                                @endif
                            </div>

                            <p class="text-xs text-gray-400">
                                IP: {{ $session->ip_address }} •

                                {{ $session->is_current_device
                                ? 'Active Now'
                                : \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans()
                               }}
                            </p>

                            {{-- ✅ NEW FEATURE: LOGIN STATUS --}}
                            <p class="text-xs mt-1">
                                <span class="px-2 py-1 text-white text-[10px] rounded {{ $session->login_status_color }}">
                                    {{ $session->login_status }}
                                </span>

                                <span class="text-gray-400 ml-2">
                                    {{ $session->login_time }}
                                </span>
                            </p>

                            <p class="text-xs text-gray-500">
                                Location: {{ $session->location ?? 'Unknown' }}
                            </p>

                        </div>

                        @if(!$session->is_current_device)
                        <form method="POST"
                            action="{{ route('sessions.revoke', $session->id) }}">
                            @csrf
                            @method('DELETE')

                            <button class="bg-red-600 px-3 py-1 rounded text-xs">
                                Revoke
                            </button>
                        </form>
                        @endif

                    </div>

                    @empty
                    <p class="p-5 text-center text-gray-400">
                        No sessions found
                    </p>
                    @endforelse

                </div>

                {{-- PAGINATION --}}
                <div class="p-5 bg-gray-950 border-t border-gray-800">
                    {{ $sessions->links('pagination::tailwind') }}
                </div>

                {{-- LOGOUT ALL --}}
                @if($sessions->total() > 1)
                <div class="p-5 text-right border-t border-gray-800">

                    <form method="POST" action="{{ route('sessions.logout-others') }}">
                        @csrf

                        <button class="bg-red-700 px-5 py-2 rounded-xl text-sm font-bold">
                            Logout Other Devices
                        </button>

                    </form>

                    <form method="POST" action="{{ route('sessions.logout-all') }}" class="mt-3">
                        @csrf

                        <button class="bg-black px-5 py-2 rounded-xl text-sm font-bold border border-gray-600">
                            Logout From All Devices
                        </button>

                    </form>

                </div>
                @endif

            </div>

        </div>
    </div>

</x-app-layout>