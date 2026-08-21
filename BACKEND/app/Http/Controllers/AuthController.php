<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
  //login
  public function login(Request $request)
  {
    $validated = $request->validate([
      'email' => 'required|email|max:255',
      'password' => 'required|string|min:8'
    ]);

    $user = User::where('email', $validated['email'])->first();

    if (!$user || !Hash::check(
      $validated['password'],
      $user->password
    )) {
      return response()->json([
        'message' => 'Invalid email or password.'
      ], 422);
    }

    if (!$user->is_active) {
      return response()->json([
        'message' => 'Your account has been deactivated.'
      ], 403);
    }

    Auth::login($user);

    return response()->json([
      'message' => 'Login successful',
      'user' => $user
    ], 200);
  }

  //Getting user
  public function user(Request $request)
  {
    return response()->json([
      'user' => $request->user()
    ], 200);
  }

  //Logout
  public function logout(Request $request)
  {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return response()->json([
      'message' => 'Logged out successfully'
    ], 200);
  }
}
