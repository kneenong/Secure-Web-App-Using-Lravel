<?php

namespace App\Http\Controllers\Auth;

// app/Http/Controllers/Auth/RegisteredUserController.php
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => [
                'required',
                'confirmed',
                Rules\Password::defaults(),
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/'
            ],
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
        ]);

        $passwordStrength = $this->calculatePasswordStrength($request->password);
        
        if ($passwordStrength < 3) {
            throw ValidationException::withMessages([
                'password' => 'Password is too weak. Please choose a stronger password.',
            ]);
        }

        $user = User::create([
            'name' => strip_tags($request->name),
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }

    private function calculatePasswordStrength(string $password): int
    {
        $score = 0;
        
        // Length check
        if (strlen($password) >= 8) $score++;
        if (strlen($password) >= 12) $score++;
        
        // Complexity checks
        if (preg_match('/[A-Z]/', $password)) $score++;
        if (preg_match('/[a-z]/', $password)) $score++;
        if (preg_match('/[0-9]/', $password)) $score++;
        if (preg_match('/[^A-Za-z0-9]/', $password)) $score++;
        
        // Entropy calculation
        $chars = count_chars($password, 1);
        $entropy = 0;
        $len = strlen($password);
        
        foreach ($chars as $count) {
            $p = $count / $len;
            $entropy -= $p * log($p, 2);
        }
        
        if ($entropy > 3.5) $score++;
        
        return min($score, 5);
    }
}