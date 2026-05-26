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

<<<<<<< HEAD
        // ✅ transform data
        $sessions->getCollection()->transform(function ($session) {

            $agent = new Agent();
            $agent->setUserAgent($session->user_agent);

            $session->browser = $agent->browser() ?? 'Unknown';
            $session->platform = $agent->platform() ?? 'Unknown';

            // ✅ IMPORTANT FIX (NO ERROR NOW)
            $session->is_current_device = ($session->id === session()->getId());

            // 🔥 SEARCH SUPPORT FIELD
            $session->search_blob = strtolower(
                $session->ip_address . ' ' .
                $session->user_agent . ' ' .
                $session->browser . ' ' .
                $session->platform
            );

            return $session;
        });

        // ✅ FILTER AFTER TRANSFORM (IMPORTANT FOR "Edge on Windows")
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

=======
        // Convert to safe objects to prevent "Undefined property" errors
                $sessions = $rawSessions->map(function ($session) {

                    $location = 'Unknown';

                    try {
                        $response = Http::get("http://ip-api.com/json/{$session->ip_address}");
                        if ($response->successful()) {
                            $data = $response->json();
                            $location = $data['city'] . ', ' . $data['country'];
                        }
                    } catch (\Exception $e) {}

                    return (object) [
                        'id'            => $session->id,
                        'ip_address'    => $session->ip_address,
                        'location'      => $location, // 👈 NEW
                        'user_agent'    => $session->user_agent ?? '',
                        'last_activity' => $session->last_activity,
                        'is_current_device' => $session->id === session()->getId(),
                    ];
                });
                $lastLogin = DB::table('sessions')
                    ->where('user_id', auth()->id())
                    ->orderBy('last_activity', 'desc')
                    ->skip(1)
                    ->first();

        return view('dashboard', compact('sessions', 'lastLogin'));
    }

    public function logoutAll()
{
    DB::table('sessions')
        ->where('user_id', auth()->id())
        ->delete();

    auth()->logout();

    return redirect('/login')->with('success', 'Logged out from all devices.');
}

    /**
     * Terminate a specific session.
     */
>>>>>>> main
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