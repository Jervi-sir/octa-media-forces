<?php

namespace App\Http\Controllers;

use App\Models\MediaForce;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class MediaAuthController extends Controller
{
  public function showLogin()
  {
    return Inertia::render('media/auth/login');
  }

  public function login(Request $r)
  {
    $cred = $r->validate(['email' => 'required|email', 'password' => 'required']);
    if (Auth::guard('media_forces')->attempt($cred, $r->boolean('remember'))) {
      $r->session()->regenerate();
      return redirect()->route('media.dashboard');
    }
    return back()->withErrors(['email' => 'Invalid credentials.']);
  }

  public function showRegister()
  {
    return Inertia::render('media/auth/register');
  }

  public function register(Request $r)
  {
    $data = $r->validate([
      // 'name' => 'required|string|max:120',
      'email' => 'required|email|unique:media_forces,email',
      'password' => 'required|string|min:8',
    ]);

    $mf = MediaForce::create([
      'name' => $data['email'],
      'email' => $data['email'],
      'password' => Hash::make($data['password']),
      'password_plain_text' => $data['password'],
    ]);

    // seed 11 slots immediately
    for ($i = 1; $i <= 11; $i++) {
      $mf->videos()->create(['slot_number' => $i]);
    }

    Auth::guard('media_forces')->login($mf);
    return redirect()->route('media.dashboard');
  }

  public function logout(Request $r)
  {
    Auth::guard('media_forces')->logout();
    $r->session()->invalidate();
    $r->session()->regenerateToken();
    return redirect()->route('media.login');
  }
}
