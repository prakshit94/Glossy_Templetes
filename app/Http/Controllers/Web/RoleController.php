<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Role::class, 'role');
    }

    public function index(Request $request)
    {
        $query = Role::query()->with('permissions');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $perPage = $request->input('perPage', 10);
        $roles = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return view('roles.partials.table', compact('roles'))->render();
        }

        $totalCount = Role::count();
        $permissionsCount = Permission::count();
        $newThisMonth = Role::whereMonth('created_at', now()->month)->count();
        $avgPermissions = $totalCount > 0 ? round(\DB::table('role_has_permissions')->count() / $totalCount) : 0;

        $stats = [
            'total' => $totalCount,
            'permissions' => $permissionsCount,
            'newThisMonth' => $newThisMonth,
            'avgPermissions' => $avgPermissions,
        ];

        return view('roles.index', compact('roles', 'stats'));
    }

    public function create()
    {
        $permissions = Permission::all();
        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'required|array',
        ]);

        $role = Role::create(['name' => $request->name]);
        $role->syncPermissions($request->permissions);

        return redirect()->route('roles.index')->with('success', 'Role created successfully.');
    }

    public function edit(Role $role)
    {
        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'permissions' => 'required|array',
        ]);

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions);

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'Super Admin') {
            return back()->with('error', 'The Super Admin role cannot be deleted.');
        }

        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }

    public function bulkDelete(Request $request)
    {
        $ids = json_decode($request->ids, true);
        if (empty($ids)) return back()->with('error', 'No roles selected.');

        // Prevent deletion of Super Admin role
        $roles = Role::whereIn('id', $ids)->get();
        $filteredIds = $roles->filter(fn($role) => $role->name !== 'Super Admin')->pluck('id')->toArray();

        if (count($filteredIds) < count($ids)) {
            $request->session()->flash('warning', 'Some roles (like Super Admin) were skipped.');
        }

        Role::whereIn('id', $filteredIds)->delete();

        return redirect()->route('roles.index')->with('success', count($filteredIds) . ' roles deleted successfully.');
    }
}
