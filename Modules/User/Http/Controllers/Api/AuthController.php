<?php

namespace Modules\User\Http\Controllers\Api;

use Dingo\Api\Http\Request;
use Dingo\Api\Routing\Helpers;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Modules\User\Entities\User;

class AuthController extends Controller
{
    use Helpers;

    protected $guard = 'api';

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth:api', ['except' => ['login']]);
        $this->middleware('refresh.token', ['except' => ['login', 'register']]);
    }

    public function register(Request $request)
    {
        $rules = [
            'name'     => ['required'],
            'email'    => ['required'],
            'password' => ['required', 'min:6', 'max:16'],
        ];

        $payload   = $request->only('name', 'email', 'password');
        $validator = Validator::make($payload, $rules);

        // 验证格式
        if ($validator->fails()) {
            return response()->array(['error' => $validator->errors()]);
        }

        // 创建用户
        $result = User::create([
            'name'     => $payload['name'],
            'email'    => $payload['email'],
            'password' => bcrypt($payload['password']),
        ]);

        if ($result) {
            return response()->json(['success' => '创建用户成功']);
        } else {
            return response()->json(['error' => '创建用户失败']);
        }

    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (! $token = $this->guard()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json($this->auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $this->auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken($this->auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => $this->guard()->factory()->getTTL() * 60
        ]);
    }

    /**
     * @return \Illuminate\Support\Facades\Auth
     */
    public function guard()
    {
        return Auth::guard($this->guard);
    }
}
