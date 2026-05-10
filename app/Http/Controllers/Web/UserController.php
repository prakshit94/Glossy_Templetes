<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Contracts\UserRepositoryInterface;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {
        $this->authorizeResource(User::class, 'user');
    }

    public function index(Request $request)
    {
        $query = User::query()->with('roles');

        if ($request->input('filter') === 'trashed') {
            $query->onlyTrashed();
        } else {
            $query->whereNull('deleted_at'); // Explicitly exclude trashed if softdeletes trait is active, though Laravel does this automatically
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('name', 'like', "%$s%")
                  ->orWhere('email', 'like', "%$s%")
                  ->orWhere('username', 'like', "%$s%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        $perPage = $request->input('perPage', 10);
        $users = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return view('users.partials.table', compact('users'))->render();
        }

        $trashedCount = User::onlyTrashed()->count();
        $activeCount = User::where('status', 'active')->count();
        $totalCount = User::count();
        $newThisMonth = User::whereMonth('created_at', now()->month)->count();
        
        $stats = [
            'total' => $totalCount,
            'active' => $activeCount,
            'newThisMonth' => $newThisMonth,
            'activePercentage' => $totalCount > 0 ? round(($activeCount / $totalCount) * 100) : 0,
        ];

        // Fetch recent activity for users
        $recentActivities = \Spatie\Activitylog\Models\Activity::where('subject_type', User::class)
            ->latest()
            ->take(5)
            ->get();

        return view('users.index', compact('users', 'trashedCount', 'activeCount', 'stats', 'recentActivities'));
    }

    public function create()
    {
        $roles = \Spatie\Permission\Models\Role::all();
        $teams = \App\Models\Team::all();
        return view('users.create', compact('roles', 'teams'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'username' => 'required|string|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'status' => 'required|string|in:active,suspended',
            'roles' => 'required|array',
            'current_team_id' => 'nullable|exists:teams,id',
        ]);

        $data['password'] = \Illuminate\Support\Facades\Hash::make($data['password']);
        $user = $this->userRepository->create($data);
        $user->syncRoles($request->roles);

        if ($request->filled('current_team_id')) {
            $user->teams()->syncWithoutDetaching([$request->current_team_id => ['role' => 'member']]);
        }

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = \Spatie\Permission\Models\Role::all();
        $userRoles = $user->roles->pluck('name')->toArray();
        $teams = \App\Models\Team::all();
        
        // Security info
        $user->loadCount(['trustedDevices', 'refreshTokens']);
        $passkeysCount = \DB::table('passkeys')->where('user_id', $user->id)->count();
        
        return view('users.edit', compact('user', 'roles', 'userRoles', 'teams', 'passkeysCount'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email,' . $user->id,
            'username' => 'required|string|unique:users,username,' . $user->id,
            'status' => 'required|string|in:active,suspended',
            'roles' => 'required|array',
            'current_team_id' => 'nullable|exists:teams,id',
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:8|confirmed']);
            $data['password'] = \Illuminate\Support\Facades\Hash::make($request->password);
        }

        $this->userRepository->update($user->id, $data);
        $user->syncRoles($request->roles);

        if ($request->filled('current_team_id')) {
            $user->teams()->syncWithoutDetaching([$request->current_team_id => ['role' => 'member']]);
        }

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();
        return back()->with('success', 'User moved to trash successfully.');
    }

    public function restore($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();
        return back()->with('success', 'User restored successfully.');
    }

    public function forceDelete($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot permanently delete your own account.');
        }

        $user->forceDelete();
        return back()->with('success', 'User permanently deleted.');
    }

    public function bulkDelete(Request $request)
    {
        $ids = json_decode($request->ids, true);
        if (empty($ids)) return back()->with('error', 'No users selected.');

        $ids = array_filter($ids, fn($id) => $id !== auth()->id());
        User::whereIn('id', $ids)->delete();

        return back()->with('success', count($ids) . ' users moved to trash.');
    }

    public function bulkRestore(Request $request)
    {
        $ids = json_decode($request->ids, true);
        if (empty($ids)) return back()->with('error', 'No users selected.');

        User::withTrashed()->whereIn('id', $ids)->restore();

        return back()->with('success', count($ids) . ' users restored.');
    }

    public function bulkForceDelete(Request $request)
    {
        $ids = json_decode($request->ids, true);
        if (empty($ids)) return back()->with('error', 'No users selected.');

        $ids = array_filter($ids, fn($id) => $id !== auth()->id());
        User::withTrashed()->whereIn('id', $ids)->forceDelete();

        return back()->with('success', count($ids) . ' users permanently deleted.');
    }

    public function bulkStatus(Request $request)
    {
        $ids = json_decode($request->ids, true);
        $status = $request->status;

        if (empty($ids)) return back()->with('error', 'No users selected.');

        User::whereIn('id', $ids)->update(['status' => $status]);

        return back()->with('success', 'Status updated for ' . count($ids) . ' users.');
    }
}
