<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Http;

class SessionController extends Controller
{
    public function index(Request $request)
    {
        $search = strtolower($request->search);

        $sessions = DB::table('sessions')
            ->where('user_id', auth()->id())
            ->orderBy('last_activity', 'desc')
            ->paginate(3)
            ->withQueryString();

        $sessions->getCollection()->transform(function ($session) {

            $agent = new Agent();
            $agent->setUserAgent($session->user_agent);

            $browser = $agent->browser() ?? 'Unknown';
            $platform = $agent->platform() ?? 'Unknown';

            // Device Type (NEW FEATURE)
            if ($agent->isDesktop()) {
                $deviceType = 'Desktop';
                $deviceIcon = '🖥️';
            } elseif ($agent->isPhone()) {
                $deviceType = 'Mobile';
                $deviceIcon = '📱';
            } else {
                $deviceType = 'Tablet';
                $deviceIcon = '💻';
            }

            $location = 'Unknown';

            try {
                if (!empty($session->ip_address)) {
                    $response = Http::timeout(3)->get(
                        "http://ip-api.com/json/{$session->ip_address}"
                    );

                    if ($response->successful()) {
                        $data = $response->json();
                        $location = trim(
                            ($data['city'] ?? '') . ', ' . ($data['country'] ?? ''),
                            ', '
                        );
                    }
                }
            } catch (\Exception $e) {
                $location = 'Unknown';
            }

            $session->browser = $browser;
            $session->platform = $platform;
            $session->location = $location;

            $sessionTime = \Carbon\Carbon::createFromTimestamp($session->last_activity);

            // Login time human readable
            $session->login_time = $sessionTime->diffForHumans();

            // LOGIN STATUS (NEW FEATURE)
            $session->login_status =
                $sessionTime->gt(now()->subHour())
                ? 'Recent Login'
                : 'Old Login';

            $session->login_status_color =
                $sessionTime->gt(now()->subHour())
                ? 'bg-green-600'
                : 'bg-gray-600';

            $session->device_type = $deviceType;   // NEW
            $session->device_icon = $deviceIcon;   // NEW

            $session->is_current_device = ($session->id === session()->getId());

            $session->search_blob = strtolower(
                $session->ip_address . ' ' .
                    $session->user_agent . ' ' .
                    $browser . ' ' .
                    $platform . ' ' .
                    $location . ' ' .
                    $deviceType
            );

            return $session;
        });

        // Search filter
        if ($search) {
            $filtered = $sessions->getCollection()->filter(function ($session) use ($search) {
                return str_contains($session->search_blob, $search);
            });

            $sessions->setCollection($filtered);
        }

        $lastLogin = DB::table('sessions')
            ->where('user_id', auth()->id())
            ->orderBy('last_activity', 'desc')
            ->skip(1)
            ->first();

        return view('dashboard', compact('sessions', 'search', 'lastLogin'));
    }

    public function revokeDevice($id)
    {
        DB::table('sessions')
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->delete();

        return back()->with('success', 'Device revoked successfully.');
    }

    public function logoutOtherDevices()
    {
        DB::table('sessions')
            ->where('user_id', auth()->id())
            ->where('id', '!=', session()->getId())
            ->delete();

        return back()->with('success', 'Other devices logged out.');
    }

    public function logoutAll()
    {
        DB::table('sessions')
            ->where('user_id', auth()->id())
            ->delete();

        auth()->logout();

        return redirect('/login')->with('success', 'Logged out from all devices.');
    }
}
