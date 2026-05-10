<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Resources\UserResource;
use App\Contracts\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {
        $this->authorizeResource(User::class, 'user');
    }

    public function index()
    {
        return UserResource::collection($this->userRepository->paginate());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'username' => 'required|string|unique:users',
            'password' => 'required|string|min:8',
            'status' => 'nullable|string|in:active,suspended',
        ]);

        $data['password'] = Hash::make($data['password']);
        $user = $this->userRepository->create($data);

        return new UserResource($user);
    }

    public function show(User $user)
    {
        return new UserResource($user);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|unique:users,email,' . $user->id,
            'username' => 'sometimes|string|unique:users,username,' . $user->id,
            'bio' => 'nullable|string',
            'status' => 'sometimes|string|in:active,suspended',
        ]);

        $this->userRepository->update($user->id, $data);

        return new UserResource($user->fresh());
    }

    public function destroy(User $user)
    {
        $this->userRepository->delete($user->id);
        return response()->json(null, 204);
    }

    public function suspend(User $user)
    {
        $this->userRepository->suspend($user);
        return response()->json(['message' => 'User suspended.']);
    }

    public function activate(User $user)
    {
        $this->userRepository->activate($user);
        return response()->json(['message' => 'User activated.']);
    }
}
