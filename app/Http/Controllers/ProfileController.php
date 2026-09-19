<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\branchDel;

class ProfileController extends Controller
{
    /**
     * Show Profile Page (Regular User or Admin View)
     */
    public function index()
    {
        $user = Auth::user();
        $isAdmin = $user->role === 'Admin';
        $users = $isAdmin ? User::all() : collect();
         $branch = branchDel::all();

        return view('profile', compact('user', 'isAdmin', 'users','branch'));
    }

    /**
     * Update Own Profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'nullable',
                'email',
                Rule::unique('users')->ignore($user->id)
            ],
            'password' => 'nullable|min:6|confirmed',
        ], [
            'name.required' => 'Name is required',
            'email.unique' => 'This email is already registered',
            'password.confirmed' => 'Password confirmation does not match',
            'password.min' => 'Password must be at least 6 characters',
        ]);

        try {
            $user->name = $validated['name'];
            $user->email = $validated['email'];

            if ($request->filled('password')) {
                $user->password = Hash::make($validated['password']);
            }

            $user->save();

            Toastr::success('Profile updated successfully', 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error('Failed to update profile: ' . $e->getMessage(), 'Error');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Admin: Create New User
     */
    public function adminStore(Request $request)
    {
        // Check if user is admin
        if (Auth::user()->role !== 'Admin') {
            Toastr::error('Unauthorized access', 'Error');
            return redirect()->back();
        }

        // Validate input
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users,username',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'role' => 'required|in:Admin,User,Manager,Cashier',
            'Branch' => 'required|string|max:255',
            'BC' => 'nullable|string|max:255',
            'password' => 'required|min:6|confirmed',
        ], [
            'username.required' => 'Username is required',
            'username.unique' => 'This username is already taken',
            'email.unique' => 'This email is already registered',
            'password.confirmed' => 'Password confirmation does not match',
            'password.min' => 'Password must be at least 6 characters',
        ]);

        try {
            // Create new user
            $user = new User();
            $user->username = $validated['username'];
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->role = $validated['role'];
            $user->Branch = $validated['Branch'];
            $user->BC = $validated['BC'] ?? null;
            $user->password = Hash::make($validated['password']);
            $user->save();

            Toastr::success('User created successfully', 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error('Failed to create user: ' . $e->getMessage(), 'Error');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Admin: Update User
     */
    public function adminUpdate(Request $request, $id)
    {
        // Check if user is admin
        if (Auth::user()->role !== 'Admin') {
            Toastr::error('Unauthorized access', 'Error');
            return redirect()->back();
        }

        $user = User::findOrFail($id);

        // Validate input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'nullable',
                'email',
                Rule::unique('users')->ignore($id)
            ],
            'role' => 'required|in:Admin,User,Manager',
            'Branch' => 'required|string|max:255',
            'BC' => 'nullable|string|max:255',
            'password' => 'nullable|min:6|confirmed',
        ], [
            'name.required' => 'Name is required',
            'email.unique' => 'This email is already registered',
            'password.confirmed' => 'Password confirmation does not match',
            'password.min' => 'Password must be at least 6 characters',
        ]);

        try {
            // Update user details
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->role = $validated['role'];
            $user->Branch = $validated['Branch'];
            $user->BC = $validated['BC'] ?? null;

            // Update password if provided
            if ($request->filled('password')) {
                $user->password = Hash::make($validated['password']);
            }

            $user->save();

            Toastr::success('User updated successfully', 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error('Failed to update user: ' . $e->getMessage(), 'Error');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Admin: Delete User
     */
    public function adminDelete($id)
    {
        // Check if user is admin
        if (Auth::user()->role !== 'Admin') {
            Toastr::error('Unauthorized access', 'Error');
            return redirect()->back();
        }

        try {
            $user = User::findOrFail($id);

            // Prevent admin from deleting themselves
            if ($user->id === Auth::id()) {
                Toastr::error('You cannot delete yourself', 'Error');
                return redirect()->back();
            }

            $userName = $user->name;
            $user->delete();

            Toastr::success("User '{$userName}' deleted successfully", 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error('Failed to delete user: ' . $e->getMessage(), 'Error');
            return redirect()->back();
        }
    }
}