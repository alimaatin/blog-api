<?php

namespace App\Http\Controllers;

use App\ApiResponseTrait;
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
            new AuthResource([
                "user"=> $user,
                "token"=> $token
            ]),
            200
        );
    }

    /**
     * Log a user in and return a token
     * @unauthenticated
     */
    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                /**
                 * Either email or username needed
                 * @example alimatin1010@gmail.com
                 */
                "email" => "required_without:username|string|email",
                /**
                 * Either username or email needed
                 * @example alimatin
                 */
                "username" => "required_without:email|string",
                /**
                 * @example alimatin
                 */
                "password" => "required|string",
                /**
                 * @example true
                 */
                "remember" => "nullable|boolean",
        ]);

        $credentials = [
            "password" => $validated["password"],
        ];

        if (isset($validated['email'])) {
            $credentials['email'] = $validated['email'];
        } else {
            $credentials['username'] = $validated['username'];
        }

        if(!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

            return response()->json(['message' => 'Login successful', 'token' => $token]);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Login failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Log the currect user out(Invalidate the token)
     */
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'User logged out successfully'],200);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Logout failed', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Return the currently logged user.
     */
    public function user()
    {
        try {
            if(!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['message' => 'User not found'], 404);
            }
            return response()->json(['message' => 'User fetched successfully', 'user' => $user],200);
        } catch (JWTException $e) {
            return response()->json(['message' => 'User fetch failed', 'error' => $e->getMessage()], 500);
        }
    }
}

