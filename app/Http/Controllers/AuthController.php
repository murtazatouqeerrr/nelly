<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
        ]);

        $user = User::create([
            'role_id' => $request->role_id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => 'active',
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'user' => $user->load('role'),
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Check if user exists and is not locked
        $user = User::where('email', $credentials['email'])->first();

        if ($user && $user->account_locked) {
            return back()->withErrors([
                'email' => 'Your account has been locked. Please contact support to regain access.',
            ])->onlyInput('email');
        }

        // Check if this is a web request (expects HTML) or API request (expects JSON)
        if ($request->expectsJson()) {
            $success = (bool) JWTAuth::attempt($credentials);

            // Log attempt
            \App\Models\LoginAttempt::create([
                'email' => $credentials['email'],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'successful' => $success,
                'attempted_at' => now(),
            ]);

            if (! $success) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            $user = auth()->user();

            return response()->json([
                'user' => $user->load('role'),
                'token' => JWTAuth::attempt($credentials),
            ]);
        }

        // Web login with session
        $success = auth()->attempt($credentials, $request->filled('remember'));

        // Log attempt
        \App\Models\LoginAttempt::create([
            'email' => $credentials['email'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'successful' => $success,
            'attempted_at' => now(),
        ]);

        if ($success) {
            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        if ($request->expectsJson()) {
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json(['message' => 'Successfully logged out']);
        }

        // Web logout
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function user()
    {
        return response()->json(auth()->user()->load('role'));
    }

    public function userWeb()
    {
        try {
            $user = auth()->user();
            if (! $user) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }

            return response()->json($user->load('role'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateProfileWeb(Request $request)
    {
        try {
            $user = auth()->user();

            $validated = $request->validate([
                'first_name' => 'sometimes|string|max:255',
                'last_name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,'.$user->id,
                'phone' => 'sometimes|string|max:20',
                'address' => 'sometimes|string',
                'city' => 'sometimes|string',
                'state' => 'sometimes|string',
                'zip_code' => 'sometimes|string',
            ]);

            $user->update($validated);

            return response()->json(['message' => 'Profile updated successfully', 'user' => $user]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
