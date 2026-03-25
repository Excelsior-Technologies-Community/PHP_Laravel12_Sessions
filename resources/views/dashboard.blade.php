<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Account Security') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded shadow-sm flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-200">
                <div class="p-8 bg-white border-b border-gray-100">
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
                        
                        <div class="p-6 hover:bg-gray-50 transition duration-150">
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
                                                <span class="px-2 py-0.5 bg-green-100 text-green-700 text-[10px] font-black uppercase rounded border border-green-200">
                                                    Current
                                                </span>
                                            @endif
                                        </div>
                                        <div class="flex items-center mt-1 text-xs text-gray-400 font-medium">
                                            <span class="bg-gray-200 text-gray-700 px-1.5 py-0.5 rounded font-mono">{{ $session->ip_address }}</span>
                                            <span class="mx-2 font-bold opacity-30">•</span>
                                            <span>{{ $isCurrent ? 'Active Now' : 'Last activity: ' . \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                </div>

                                @if(!$isCurrent)
                                    <form action="{{ route('sessions.revoke', $session->id) }}" method="POST" onsubmit="return confirm('Revoke access for this device?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-bold text-red-600 hover:bg-red-50 px-3 py-2 rounded-lg transition">
                                            Revoke Access
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                @if(count($sessions) > 1)
                    <div class="p-8 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                        <p class="text-xs text-gray-500 font-medium max-w-sm">Logout from all other sessions at once for maximum security.</p>
                        <form action="{{ route('sessions.logout-others') }}" method="POST" onsubmit="return confirm('Disconnect all other devices?')">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-6 py-3 bg-red-600 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition shadow-lg hover:shadow-red-200">
                                Terminate All Others
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>