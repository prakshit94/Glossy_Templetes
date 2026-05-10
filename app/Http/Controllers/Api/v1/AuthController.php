<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Contracts\AuthServiceInterface;
use App\DTOs\LoginDTO;
use App\DTOs\RegistrationDTO;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    public function __construct(
        protected AuthServiceInterface $authService
    ) {}

    #[OA\Post(
        path: "/v1/login",
        summary: "User login",
        tags: ["Auth"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["login", "password"],
                properties: [
                    new OA\Property(property: "login", type: "string", example: "admin@example.com"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "Admin@123456"),
                    new OA\Property(property: "device_name", type: "string", example: "mobile")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Success"),
            new OA\Response(response: 401, description: "Unauthorized")
        ]
    )]
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
            'device_name' => 'nullable|string',
        ]);

        $dto = LoginDTO::fromRequest($request->all());
        $result = $this->authService->login($dto);

        if (isset($result['requires_mfa'])) {
            return response()->json([
                'message' => 'MFA verification required.',
                'requires_mfa' => true,
                'user_id' => $result['user_id']
            ], 200);
        }

        return response()->json([
            'user' => new UserResource($result['user']),
            'access_token' => $result['access_token'],
            'refresh_token' => $result['refresh_token'],
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $dto = RegistrationDTO::fromRequest($request->all());
        $user = $this->authService->register($dto);

        return response()->json([
            'message' => 'User registered successfully.',
            'user' => new UserResource($user),
        ], 201);
    }

    public function user(Request $request)
    {
        return new UserResource($request->user());
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request->user());
        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function refreshToken(Request $request)
    {
        $request->validate(['refresh_token' => 'required|string']);
        
        try {
            $result = $this->authService->refreshTokens($request->refresh_token);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }
    }
}
