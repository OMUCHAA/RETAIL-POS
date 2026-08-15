<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::latest()->paginate(10);

        return response()->json([
            'users' => $users
        ], 200);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|emailmax:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:cashier,admin,manager'
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password']
        ]);

        $user->role = $validated['role'];
        $user->save();

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return response()->json([
            'user' => $user
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:admin,manager,cashier'
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];

        //If password was changed too which is not a must.
        if (!empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        return response()->json([
            'message'=> 'User updated successfully',
            'user'=> $user
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function deactivate(User $user)
    {
        $user->is_active = false;
        $user->save();

        return response()->json([
            'message'=> 'User deactivated successfully'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function reactivate(User $user)
    {
        $user->is_active = true;
        $user->save();

        return response()->json([
            'message'=> 'User activated successfully'
        ]);
    }
}
