<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * List all users.
     */
    public function index()
    {
        $users = Admin::with(['roles', 'permissions'])->get();
        return response()->json($users);
    }

    /**
     * Show a single user by ID.
     */
    public function show($id)
    {
        $user = Admin::with(['roles', 'permissions'])->findOrFail($id);
        // Append all permissions (direct + role-based)
        $user->all_permissions = $user->getAllPermissions();
        return response()->json($user);
    }

    /**
     * Create a new user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:admins,username',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|string|min:6',
            'role' => 'required|string', // Removed exists:roles,name
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
            'phone' => 'nullable|string|max:20',
            'status' => 'nullable|in:active,inactive',
            'level' => 'nullable|integer|min:1',
        ]);

        $user = Admin::create([
            'name' => $request->full_name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'status' => $request->status ?? 'active',
            'level' => $request->level,
        ]);

        // ✅ Dynamic Role Creation
        $role = Role::firstOrCreate(['name' => $request->role, 'guard_name' => 'web']);
        
        // ✅ Sync permissions to the role if provided
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        $user->assignRole($role);
        
        return response()->json([
            'message' => 'User created successfully',
            'user' => $user->load(['roles', 'permissions'])
        ]);
    }

    /**
     * Update an existing user.
     */
    public function update(Request $request, $id)
    {
        $user = Admin::findOrFail($id);

        $request->validate([
            'full_name' => 'required|string|max:255',
            'username' => "required|string|max:255|unique:admins,username,{$user->id}",
            'email' => "required|email|unique:admins,email,{$user->id}",
            'password' => 'nullable|string|min:6',
            'role' => 'required|string', // Removed exists:roles,name
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
            'phone' => 'nullable|string|max:20',
            'status' => 'nullable|in:active,inactive',
            'level' => 'nullable|integer|min:1',
        ]);

        $user->update([
            'name' => $request->full_name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
            'phone' => $request->phone,
            'status' => $request->status ?? $user->status,
            'level' => $request->level ?? $user->level,
        ]);

        // ✅ Dynamic Role Creation/Update
        $role = Role::firstOrCreate(['name' => $request->role, 'guard_name' => 'web']);
        
        // ✅ Sync permissions to the role if provided
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        $user->syncRoles([$role]);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user->load(['roles', 'permissions'])
        ]);
    }

    /**
     * Delete a user.
     */
    public function destroy($id)
    {
        $user = Admin::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    public function resetPassword(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|string|min:6',
        ]);

        $user = Admin::findOrFail($id);
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(['message' => 'Password reset successfully.']);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];

        if (!empty($validatedData['password'])) {
            $user->password = Hash::make($validatedData['password']);
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }
}
