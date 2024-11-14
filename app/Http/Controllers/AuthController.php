<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\ResponseTrait;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{

    use ResponseTrait;
    public function __construct()
    {
        $this->middleware('auth:api', [
            'except' => [
                'login',
                'register',
            ],
        ]);
    }

    public function login(Request $request)
    {
        try {
            if (!$request->email) {
                $message = "Email field is required!";
                return $this->responseError(403, false, $message);
            }

            if (!$request->password) {
                $message = "Password field is required!";
                return $this->responseError(403, false, $message);
            }

            if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
                $message = "Invalid email format!";
                return $this->responseError(403, false, $message);
            }

            $userExist = User::where('email', $request->email)->first();

            if (!$userExist) {
                $message = "User does not exist!";
                return $this->responseError(403, false, $message);
            }

            if (!Hash::check($request->password, $userExist->password)) {
                $message = "Incorrect password!";
                return $this->responseError(403, false, $message);
            }

            // Generate JWT token
            $token = JWTAuth::attempt(['email' => $request->email, 'password' => $request->password], ['exp' => Carbon::now()->addMinutes(1024)->timestamp]);
            if (!$token) {
                $message = "Failed to generate token!";
                return $this->responseError(403, false, $message);
            }

            // If everything is correct, return the token
            return $this->createNewToken($token);
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage(), []);
        }
    }

    public function register(Request $request)
    {
        $userExist = User::where('phone', $request->phone)
            ->select(['id', 'name', 'phone', 'email', 'password'])
            ->first();

        if ($userExist && $userExist->is_phone_verified == 1) {
            if (Hash::check($request->password, $userExist->password)) {
                $message = "Already registered, now you can login";
                return $this->responseError(403, false, $message);
            } else {
                $message = "This phone number is already registered!";
                return $this->responseError(403, false, $message);
            }
        }

        if ($request->type != null) {
            $type = strtolower($request->type);
            $request->validate([
                'name' => 'required|string|max:50',
                'email' => 'required|string|max:50',
                'phone' => 'required|max:11|min:11|regex:/(01)[0-9]{9}/|unique:users',
                'type' => 'required',
                'password' => 'required|string|min:8',
                'roles' => 'required|exists:roles,id',
            ]);
        } else {
            $message = "Type cannot be null";
            return $this->responseError(400, false, $message);
        }
        $defaultCompanyId = 1;

        try {
            $user = User::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'type' => strtolower($request->type),
                'password' => Hash::make($request->password),
                'default_company_id' => $defaultCompanyId,
                // 'roles' => $request->roles,
            ]);

            $role = Role::find($request->roles);
            if ($role) {
                $user->assignRole($role->name);
            } else {
                return $this->responseError(400, false, "Role does not exist");
            }

            $message = "User Registration Successfully Done";
            return $this->responseSuccess(200, true, $message, $user);
        } catch (QueryException $e) {
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }

    public function refresh()
    {
        return $this->createNewToken(auth()->refresh());
    }
    protected function createNewToken($token)
    {
        $user = auth()->user();

        $roles = $user->roles()->pluck('name');

        // Retrieve permissions and log them
        // $permissions = $user->getAllPermissions(); // No pluck here for debugging
        // \Log::info('User permissions:', ['permissions' => $permissions]);

        return response()->json([
            'status_code' => 200,
            'message' => 'Login Successful',
            'status' => true,
            'data' => [
                'user' => $user->only(['id', 'name', 'phone', 'email']),
                'role' => $roles,
                // 'permissions' => optional($permissions)->pluck('name') ?? collect(),
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => Carbon::now()->addMinutes(1440),
            ],
        ]);
    }

}
