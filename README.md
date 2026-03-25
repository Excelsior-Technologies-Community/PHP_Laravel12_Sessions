# PHP_Laravel12_Sessions

## Project Description

**PHP_Laravel12_Sessions** is a Laravel 12 application that demonstrates how to build a **Browser Session Management & Security Dashboard** using Laravel Breeze authentication and the [browser-sessions] package by **cjmellor**.

Logged-in users can view all active sessions (devices) on their account, see device type (Desktop / Mobile / Tablet), browser name, platform, IP address, and last activity time. Users can **revoke access** for a specific device or **terminate all other sessions** at once — all from a clean Security Dashboard UI.

This project is **beginner-friendly** and helps understand how to manage database-driven sessions securely in Laravel.

---

## Features

- 🔐 Full Authentication System via Laravel Breeze (Login, Register, Password Reset)
- 🖥️ View all active sessions / logged-in devices
- 📱 Device type detection — Desktop 🖥️, Mobile 📱, Tablet 💻
- 🌐 Shows Browser name, Platform (OS), IP Address, and Last Activity time
- ✅ Highlights the **Current Session** with a green badge
- ❌ Revoke access for a specific device (Single Session Logout)
- 🔴 Terminate all other sessions at once (Bulk Logout)
- 💬 Success messages after every action
- 🗄️ Database-driven sessions (`SESSION_DRIVER=database`)
- 🎨 Clean Tailwind CSS UI built on top of Breeze

---

## Technologies Used

| Technology | Purpose |
|---|---|
| PHP 8.1+ | Backend Language |
| Laravel 12 | PHP Framework |
| MySQL | Database |
| Laravel Breeze | Authentication Scaffolding |
| browser-sessions (cjmellor) | Session management package |
| Jenssegers Agent | Device and browser detection |
| Tailwind CSS | UI Styling |
| Blade Templates | Frontend Views |

---

## How It Works

```
User logs in  →  Session saved to DB  →  Dashboard shows all sessions  →  User can revoke any device! 🔐
```

1. User registers and logs in via Laravel Breeze.
2. Each login creates a row in the `sessions` database table.
3. The `SessionController` fetches all sessions for the logged-in user.
4. Jenssegers Agent detects device type, browser, and platform from the `user_agent` field.
5. User can revoke a specific session or log out all other devices from the dashboard.

---

## Installation Steps

---

### STEP 1: Create Laravel 12 Project

Open terminal / CMD and run:

```bash
composer create-project laravel/laravel PHP_Laravel12_Sessions "12.*"
```

Go inside the project folder:

```bash
cd PHP_Laravel12_Sessions
```

> This installs a fresh Laravel 12 project and moves into the project folder.

---

### STEP 2: Database Setup

Update `.env` with your database details:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=php_laravel12_sessions
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
```

Create database in MySQL / phpMyAdmin:

```
Database name: php_laravel12_sessions
```

> Setting `SESSION_DRIVER=database` tells Laravel to store all session data in the `sessions` table instead of files.

---

### STEP 3: Install Laravel Breeze

```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
```

> Installs Laravel Breeze with Blade frontend — provides Login, Register, Dashboard, and Profile pages out of the box.

---

### STEP 4: Create Sessions Table

```bash
php artisan session:table
```

> Generates the migration file for the `sessions` database table.

---

### STEP 5: Run Migrations

```bash
php artisan migrate
```

> Creates all tables: `users`, `sessions`, `cache`, `jobs`, and any other default Laravel tables.

---

### STEP 6: Install the Browser Sessions Package

```bash
composer require cjmellor/browser-sessions
```

> Installs the `browser-sessions` package which provides helper classes for managing user sessions stored in the database.

---

### STEP 7: Publish Package Files

```bash
php artisan vendor:publish
```

Select the `cjmellor/browser-sessions` option when prompted.

> Publishes the package config and any required files into your project.

---

### STEP 8: Install Frontend Dependencies

```bash
npm install
npm run build
```

> Compiles Tailwind CSS and JavaScript assets using Vite.

---

### STEP 9: Create the Session Controller

Run:

```bash
php artisan make:controller SessionController
```

Open: `app/Http/Controllers/SessionController.php`

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jenssegers\Agent\Agent;

class SessionController extends Controller
{
    /**
     * Show the Security Dashboard with all active sessions.
     */
    public function index()
    {
        $rawSessions = DB::table('sessions')
            ->where('user_id', auth()->id())
            ->orderBy('last_activity', 'desc')
            ->get();

        // Map to safe objects to prevent "Undefined property" errors
        $sessions = $rawSessions->map(function ($session) {
            return (object) [
                'id'                => $session->id,
                'ip_address'        => $session->ip_address,
                'user_agent'        => $session->user_agent ?? '',
                'last_activity'     => $session->last_activity,
                'is_current_device' => $session->id === session()->getId(),
            ];
        });

        return view('dashboard', compact('sessions'));
    }

    /**
     * Terminate a specific session by ID.
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
```

> `index()` — fetches all sessions for the logged-in user and passes them to the dashboard view.
> `revokeDevice()` — deletes a specific session row from the database.
> `logoutOtherDevices()` — deletes all session rows except the current session.

---

### STEP 10: Add Routes

Open: `routes/web.php`

```php
<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SessionController;
use Illuminate\Support\Facades\Route;

// Home Page
Route::get('/', function () {
    return view('welcome');
});

// Dashboard — handled by SessionController
Route::get('/dashboard', [SessionController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Authenticated Routes
Route::middleware('auth')->group(function () {

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Revoke a specific session (Single Logout)
    Route::delete('/sessions/revoke/{id}', [SessionController::class, 'revokeDevice'])
        ->name('sessions.revoke');

    // Terminate all other sessions (Bulk Logout)
    Route::post('/sessions/logout-others', [SessionController::class, 'logoutOtherDevices'])
        ->name('sessions.logout-others');
});

require __DIR__.'/auth.php';
```

> Dashboard route uses `SessionController` to load active sessions data.
> Two dedicated routes handle single device revoke and bulk logout.

---

### STEP 11: Update Dashboard Blade View

Replace the content of: `resources/views/dashboard.blade.php`

```html
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
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-200">

                <!-- Header -->
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

                <!-- Session List -->
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

                                    <!-- Device Icon -->
                                    <div class="w-12 h-12 flex items-center justify-center bg-gray-100 rounded-xl text-2xl shadow-inner">
                                        @if($agent->isDesktop()) 🖥️
                                        @elseif($agent->isPhone()) 📱
                                        @else 💻
                                        @endif
                                    </div>

                                    <!-- Session Info -->
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

                                <!-- Revoke Button (hidden for current session) -->
                                @if(!$isCurrent)
                                    <form action="{{ route('sessions.revoke', $session->id) }}" method="POST"
                                          onsubmit="return confirm('Revoke access for this device?')">
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

                <!-- Terminate All Others Button -->
                @if(count($sessions) > 1)
                    <div class="p-8 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                        <p class="text-xs text-gray-500 font-medium max-w-sm">
                            Logout from all other sessions at once for maximum security.
                        </p>
                        <form action="{{ route('sessions.logout-others') }}" method="POST"
                              onsubmit="return confirm('Disconnect all other devices?')">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-6 py-3 bg-red-600 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition shadow-lg">
                                Terminate All Others
                            </button>
                        </form>
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>
```

> Displays all active sessions with device icon, browser, platform, IP, and last activity.
> Current session is highlighted with a green "Current" badge and cannot be revoked.
> "Terminate All Others" button appears only when more than 1 session is active.

---

### STEP 12: Run the Application

Start the development server:

```bash
php artisan serve
```

Open in browser:

```
http://127.0.0.1:8000
```

Register a new account, log in, and visit the dashboard to see your active sessions.

---

## Expected Output

| URL | What You See |
|---|---|
| `http://127.0.0.1:8000` | Laravel welcome page |
| `http://127.0.0.1:8000/register` | Registration page (Breeze) |
| `http://127.0.0.1:8000/login` | Login page (Breeze) |
| `http://127.0.0.1:8000/dashboard` | Security dashboard with active sessions list |

### Dashboard Shows:

| Column | Description |
|---|---|
| Device Icon | 🖥️ Desktop / 📱 Phone / 💻 Tablet |
| Browser & OS | e.g., `Chrome on Windows` |
| IP Address | Client IP shown in monospace badge |
| Last Activity | `Active Now` for current, `X minutes ago` for others |
| Revoke Button | Appears only for non-current sessions |
| Terminate All | Appears only when 2+ sessions are active |

---
<img width="1918" height="955" alt="Screenshot 2026-03-25 112812" src="https://github.com/user-attachments/assets/3917bee0-327c-4a44-a5a4-12288cfe4350" />
<img width="1918" height="964" alt="Screenshot 2026-03-25 112825" src="https://github.com/user-attachments/assets/d559d7e3-4543-4e4d-80e3-1f8e3fa3a44e" />
<img width="1919" height="839" alt="Screenshot 2026-03-25 112842" src="https://github.com/user-attachments/assets/c1519908-3053-4b46-b2df-5cbeeb15a56e" />
<img width="1916" height="906" alt="Screenshot 2026-03-25 112858" src="https://github.com/user-attachments/assets/62b19d12-2405-4882-a914-01ea11251f8f" />
<img width="1919" height="911" alt="Screenshot 2026-03-25 112919" src="https://github.com/user-attachments/assets/fdb17af7-1e3f-4cdf-b820-37b86d45e471" />



## Project Folder Structure

```
PHP_Laravel12_Sessions/
│
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── SessionController.php      ← index, revokeDevice, logoutOtherDevices
│   │       └── ProfileController.php      ← Installed by Breeze
│   │
│   └── Models/
│       └── User.php
│
├── database/
│   └── migrations/
│       ├── xxxx_create_users_table.php
│       ├── xxxx_create_sessions_table.php  ← Created by php artisan session:table
│       ├── xxxx_create_cache_table.php
│       └── xxxx_create_jobs_table.php
│
├── resources/
│   └── views/
│       ├── dashboard.blade.php            ← Security dashboard (sessions list)
│       ├── auth/                          ← Login, Register views (Breeze)
│       ├── profile/                       ← Profile edit view (Breeze)
│       ├── layouts/                       ← App layout (Breeze)
│       └── components/                    ← Blade components (Breeze)
│
├── routes/
│   ├── web.php                            ← All routes including session routes
│   └── auth.php                           ← Auth routes (Breeze)
│
├── .env                                   ← SESSION_DRIVER=database
├── artisan
├── composer.json
├── package.json
└── README.md
```

---

## Useful Commands

| Command | Purpose |
|---|---|
| `composer require laravel/breeze --dev` | Install Laravel Breeze package |
| `php artisan breeze:install blade` | Scaffold Breeze with Blade frontend |
| `php artisan session:table` | Generate the sessions table migration |
| `php artisan migrate` | Run all migrations |
| `composer require cjmellor/browser-sessions` | Install browser-sessions package |
| `php artisan vendor:publish` | Publish package files |
| `php artisan make:controller SessionController` | Create the SessionController |
| `npm install && npm run build` | Install and build frontend assets |
| `php artisan serve` | Start the local development server |

---

