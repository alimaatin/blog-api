<?php

namespace App\Http\Controllers;

use App\ApiResponseTrait;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\AuthResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    use ApiResponseTrait;

    /**
     * Create a user and return a token
     * @unauthenticated
     */
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([$validated]);

        $token = JWTAuth::fromUser($user);

        return $this->successResponse(
            compact($token),
            200
        );
    }

    /**
     * Log a user in and return a token
     * @unauthenticated
     */
    public function login(LoginRequest $request)
    {
        try {
            $validated = $request->validated();

            $credentials = [
                "username" => $validated["username"] ?? $validated["email"],
                "password" => $validated["password"],
            ];

            if(!$token = JWTAuth::attempt($credentials)) {
                return $this->errorResponse('Invalid credentials', 401);
            }

            return $this->successResponse(compact($token), 200);
        } catch (JWTException $e) {
            return $this->errorResponse('Login failed', 500);
        }
    }

    /**
     * Log the currect user out(Invalidate the token)
     */
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return $this->successResponse(['message' => 'User logged out successfully'], 200);
        } catch (JWTException $e) {
            return $this->errorResponse('Logout failed', 500);
        }
    }

    /**
     * Return the currently logged user.
     */
    public function user()
    {
        try {
            if(!$user = JWTAuth::parseToken()->authenticate()) {
                return $this->errorResponse('User not found', 404);
            }
            return $this->successResponse(['user' => $user], 200);
        } catch (JWTException $e) {
            return $this->errorResponse('User fetch failed', 500);
        }
    }
}

