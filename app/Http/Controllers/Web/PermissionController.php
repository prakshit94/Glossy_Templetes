<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index()
    {
        $this->authorize('permissions.view');
        $permissions = Permission::all()->groupBy(fn($p) => explode('.', $p->name)[0]);
        return view('permissions.index', compact('permissions'));
    }
}
