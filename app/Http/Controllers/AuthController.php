<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }
    public function login()
    {
        $credentials = request(['email', 'password']);
        if (!$token = auth('api')->claims(['scope' => ['customer', 'invoices']])->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        // dùng phương thức setTTL để điều chỉnh ttl của token một cách tự nguyện
        // $token = auth()->setTTL(120)->login($user)

        $refreshToken = $this->createRefreshToken();

        return $this->respondWithToken($token, $refreshToken);
    }
    public function refresh()
    {
        $refreshToken = request()->refresh_token;
        try {
            $decoded = JWTAuth::getJWTProvider()->decode($refreshToken);
            $user = User::find($decoded['user_id']);
            if (!$user) {
                return response()->json(['erorr' => 'user not found'], 404);
            }
            auth('api')->invalidate();

            $token = auth('api')->login($user);
            // return response()->json($decoded);
            $newRefreshToken = $this->createRefreshToken();
            return $this->respondWithToken($token, $newRefreshToken);
        } catch (JWTException $exception) {
            return response()->json(['error' => 'Refresh token invalid'], 500);
        }

        // return $this->respondWithToken(auth('api')->refresh());
    }
    public function logout()
    {
        auth('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function profile()
    {
        try {
            $payload = auth('api')->payload();
            $payload['scope'];
            return response()->json($payload['scope']);

            // return response()->json(auth('api')->user());
        } catch (JWTException $exception) {
            return response()->json(['erorr' => 'token invalid'], 404);
        }
        return response()->json(auth('api')->user());
    }
    private function createRefreshToken()
    {
        $data = [
            // 'user_id' => auth('api')->user()->id,
            'user_id' => auth('api')->user()->id,

            'random' => rand() . time(),
            'exp' => time() + config('jwt.refresh_ttl'),
        ];
        $refreshToken = JWTAuth::getJWTProvider()->encode($data);
        return $refreshToken;
    }
    private function respondWithToken($token, $refreshToken)
    {
        return response()->json([
            'access_token' => $token,
            'refresh_token' => $refreshToken,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
        ]);
    }
}
