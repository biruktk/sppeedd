<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = Admin::with('roles', 'permissions')->get();
        $roles = Role::all();
        $permissions = Permission::all();
        
        return response()->json([
            'users' => $users,
            'roles' => $roles,
            'permissions' => $permissions
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:admins',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:6',
            'roles' => 'array',
            'permissions' => 'array'
        ]);

        $user = Admin::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        if ($request->roles) {
            $roles = collect($request->roles)->map(function ($roleName) {
                return Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            });
            $user->syncRoles($roles);
        }

        if ($request->permissions) {
            $user->syncPermissions($request->permissions);
        }

        return response()->json(['message' => 'User created successfully', 'user' => $user]);
    }

    public function update(Request $request, Admin $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:admins,username,' . $user->id,
            'email' => 'required|string|email|max:255|unique:admins,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'roles' => 'array',
            'permissions' => 'array'
        ]);

        $user->update([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email
        ]);

        if ($request->password) {
            $user->password = Hash::make($request->password);
            $user->save();
        }

        if ($request->roles) {
            $roles = collect($request->roles)->map(function ($roleName) {
                return Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            });
            $user->syncRoles($roles);
        }

        if ($request->permissions) {
            $user->syncPermissions($request->permissions);
        }

        return response()->json(['message' => 'User updated successfully', 'user' => $user]);
    }

    public function destroy(Admin $user)
    {
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }

    public function getRoles()
    {
        $roles = Role::with('permissions')->get();
        return response()->json($roles);
    }

    public function storeRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'permissions' => 'array'
        ]);

        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);

        if ($request->permissions) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json(['message' => 'Role created successfully', 'role' => $role]);
    }

    public function updateRole(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'array'
        ]);

        $role->update(['name' => $request->name]);

        if ($request->permissions) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json(['message' => 'Role updated successfully', 'role' => $role]);
    }

    public function destroyRole(Role $role)
    {
        $role->delete();
        return response()->json(['message' => 'Role deleted successfully']);
    }

    public function getPermissions()
    {
        $permissions = Permission::all()->groupBy(function ($permission) {
            return explode('-', $permission->name)[0];
        });
        
        return response()->json($permissions);
    }
}
