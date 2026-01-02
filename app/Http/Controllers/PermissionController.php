<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::all();
        return response()->json($permissions);
    }

    public function getByRole($roleId)
    {
        $role = \Spatie\Permission\Models\Role::findById($roleId, 'web');
        $permissions = $role->permissions;
        return response()->json($permissions);
    }
}
