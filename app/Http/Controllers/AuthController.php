<?php

namespace App\Http\Controllers;

use App\Models\User;

use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Hash;
use Illuminate\Database\QueryException;


class AuthController extends Controller
{

    use ResponseTrait;
    public function __construct()
    {
        $this->middleware('auth:api', [
            'except' => [
                'login',
               
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

            // $regex = '/^[^0-9][_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
            // if (preg_match($regex, $request->email)) {
            //     $message = "Invalid email format!";
            //     return $this->responseError(403, false, $message);
            // }

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
                $message = "This phone number already registered!";
                return $this->responseError(403, false, $message);
            }
        }

        if ($request->type != null) {
            // converts all the uppercase English alphabets present in the string to lowercase
            $type = strtolower($request->type);
            if ($type === 'user') {
                $request->validate([
                    'name' => 'required|string|max:50',
                    'email' => 'required|string|max:50',
                    'phone' => 'required|max:11|min:11|regex:/(01)[0-9]{9}/|unique:users',
                    'type' => 'required',
                    'password' => 'required|string|min:8', // Add password validation
                ]);
            }
        } else {
            $message = "Type cannot be null";
            return $this->responseError(400, false, $message);
        }

        try {

            $user = User::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'type' => strtolower($request->type),
                'password' => Hash::make($request->password), // Hash the password before saving
            ]);
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

        return response()->json([
            'status_code' => 200,
            'message' => 'Login Succesfull',
            'status' => true,
            'data' => [
                'user' => auth()->user()->only(
                    [
                        'id',
                        'name',
                        'phone',
                        'email',
                    ]
                ),

                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => Carbon::now()->addMinutes(1440),
            ],

        ]);
    }
}
