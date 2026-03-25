<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jenssegers\Agent\Agent;

class SessionController extends Controller
{
    /**
     * Show the Security Dashboard with Active Sessions.
     */
    public function index()
    {
        $rawSessions = DB::table('sessions')
            ->where('user_id', auth()->id())
            ->orderBy('last_activity', 'desc')
            ->get();

        // Convert to safe objects to prevent "Undefined property" errors
        $sessions = $rawSessions->map(function ($session) {
            return (object) [
                'id'            => $session->id,
                'ip_address'    => $session->ip_address,
                'user_agent'    => $session->user_agent ?? '',
                'last_activity' => $session->last_activity,
                'is_current_device' => $session->id === session()->getId(),
            ];
        });

        return view('dashboard', compact('sessions'));
    }

    /**
     * Terminate a specific session.
     */
    public function revokeDevice($id)
    {
        DB::table('sessions')
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->delete();

        return back()->with('success', 'Device disconnected successfully.');
    }

    /**
     * Terminate all sessions except the current one.
     */
    public function logoutOtherDevices()
    {
        DB::table('sessions')
            ->where('user_id', auth()->id())
            ->where('id', '!=', session()->getId())
            ->delete();

        return back()->with('success', 'All other devices have been logged out.');
    }
}