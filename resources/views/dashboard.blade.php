<x-app-layout>

    <x-slot name="header">
        <h2 class="font-bold text-xl text-white">
            🔐 Account Security Dashboard
        </h2>
    </x-slot>

    <div class="py-10 bg-gray-950 min-h-screen text-white">

<<<<<<< HEAD
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
                    <h3 class="text-2xl font-bold text-blue-400">{{ $sessions->total() }}</h3>
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

                {{-- HEADER --}}
                <div class="p-5 border-b border-gray-800">
                    <h3 class="font-bold text-lg">Login Activity</h3>
                    <p class="text-xs text-gray-400">Manage your devices</p>
                </div>

                {{-- LIST --}}
                <div class="divide-y divide-gray-800">

                    @forelse($sessions as $session)

                        <div class="p-5 flex justify-between hover:bg-gray-800">

                            <div>

                                <p class="font-semibold">
                                    {{ $session->browser }} on {{ $session->platform }}
                                </p>

                                <p class="text-xs text-gray-400">
                                    IP: {{ $session->ip_address }} •
                                    {{ $session->is_current_device
                                        ? 'Active Now'
                                        : \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans()
                                    }}
                                </p>

=======
            <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-200">
                <div class="p-8 bg-white border-b border-gray-100">
                    @if($lastLogin)
                        <p class="text-xs text-gray-400 mt-2">
                            Last login: 
                            {{ \Carbon\Carbon::createFromTimestamp($lastLogin->last_activity)->diffForHumans() }}
                        </p>
                    @endif
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-extrabold text-gray-900 tracking-tight">Login Activity</h3>
                            <p class="text-sm text-gray-500 mt-1">Manage individual devices logged into your account.</p>
                        </div>
                        <div class="bg-blue-50 text-blue-700 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest shadow-sm">
                            {{ count($sessions) }} Devices Active
                        </div>
                    </div>
                </div>

                <div class="divide-y divide-gray-100">
                    @foreach($sessions as $session)
                        @php
                            $agent = new \Jenssegers\Agent\Agent();
                            $agent->setUserAgent($session->user_agent);
                            $isCurrent = $session->id === session()->getId();
                        @endphp
                        
                      <div class="p-6 hover:bg-blue-50 hover:shadow-lg transition duration-300 rounded-xl">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 flex items-center justify-center bg-gray-100 rounded-xl text-2xl shadow-inner">
                                        @if($agent->isDesktop()) 🖥️ @elseif($agent->isPhone()) 📱 @else 💻 @endif
                                    </div>

                                    <div>
                                        <div class="flex items-center space-x-2">
                                            <h4 class="text-sm font-bold text-gray-800">
                                                {{ $agent->browser() }} on {{ $agent->platform() }}
                                            </h4>
                                            @if($isCurrent)
                                                <span class="px-3 py-1 bg-green-500 text-white text-[10px] font-bold uppercase rounded-full shadow">
                                                    Current
                                                </span>
                                            @endif
                                        </div>
                                        <div class="flex items-center mt-1 text-xs text-gray-400 font-medium">
                                            <span class="bg-gray-200 text-gray-700 px-1.5 py-0.5 rounded font-mono">{{ $session->ip_address }}</span>
                                          <span class="mx-2 font-bold opacity-30">•</span>
                                                <span>{{ $session->location }}</span>
                                            <span>{{ $isCurrent ? 'Active Now' : 'Last activity: ' . \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                </div>

                                @if(!$isCurrent)
                                    <form action="{{ route('sessions.revoke', $session->id) }}" method="POST" onsubmit="return confirm('Revoke access for this device?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-bold text-white bg-red-500 hover:bg-red-600 px-4 py-2 rounded-lg shadow">
                                            Revoke Access
                                        </button>
                                    </form>
                                @endif
>>>>>>> main
                            </div>

                            @if(!$session->is_current_device)
                                <form method="POST" action="{{ route('sessions.revoke', $session->id) }}">
                                    @csrf
                                    @method('DELETE')

                                    <button class="bg-red-600 px-3 py-1 rounded text-xs">
                                        Revoke
                                    </button>
                                </form>
                            @endif

                        </div>

                    @empty
                        <p class="p-5 text-center text-gray-400">No sessions found</p>
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
<<<<<<< HEAD

                            <button class="bg-red-700 px-5 py-2 rounded-xl text-sm font-bold">
                                Logout All Other Devices
=======
                            <button type="submit" class="inline-flex items-center px-6 py-3 bg-red-600 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition shadow-lg hover:shadow-red-200">
                                Terminate All Others
                                <form action="/logout-all" method="POST">
                                    @csrf
                                    <button class="mt-3 px-6 py-3 bg-black text-white rounded-xl text-xs font-bold">
                                        Logout From All Devices
                                    </button>
                                </form>
>>>>>>> main
                            </button>
                        </form>

                    </div>
                @endif

            </div>

        </div>
    </div>

</x-app-layout>