<?php

namespace App\Http\Controllers\Backend;

use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use App\Http\Controllers\Controller;
use App\Models\ParentPermission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;


class UserController extends Controller
{
    use ResponseTrait;


    public function changePassword(Request $request)
    {
        try {

            $request->validate([
                'password' => 'required|min:8',
                'confirm_password' => 'required|same:password',
            ]);

            $user = User::where('id', $request->id)->first();

            if ($user) {

                $userData = $user->update([
                    'password' => Hash::make($request->password),
                ]);

                if ($userData) {
                    $message = "Your Password Updated Successfully";
                    return $this->responseSuccess(200, true, $message, $user);
                } else {
                    $message = "Failed to update password.";
                    return $this->responseError(500, false, $message);
                }
            } else {
                $message = "User not found.";
                return $this->responseError(404, false, $message);
            }
        } catch (ValidationException $e) {
            $message = $e->getMessage() ?: 'Validation failed';

            return $this->responseError(422, false, $message);
        }
    }

    public function profileImageUpdate(Request $request)
    {
        $userData = User::where('id', auth()->user()->id)->first();
        if ($request->hasFile('profile_image')) {
            $image = $request->file('profile_image');
            $uniqueCode = str_pad(mt_rand(1, 99999), 3, '0', STR_PAD_LEFT);
            $productImageName = time() . $uniqueCode . '.' . $image->getClientOriginalExtension();
            Storage::putFileAs('public/profile_image', $image, $productImageName);
            // Delete old image if exists
            if ($userData->product_image) {
                Storage::delete('public/profile_image/' . $userData->profile_image);
            }
            $userData->update(['profile_image' => $productImageName]);
            $message = "Your Profile Image Updated Successfully";
            return $this->responseSuccess(200, true, $message, $userData);
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            // Validate the request data
            $request->validate([
                'name' => 'required|string',
                'phone' => 'required|string',
                'address' => 'nullable|string',
                'email' => 'string'
            ]);


            $userData = User::where('id', auth()->user()->id)->first();

            // Update user details
            $userData->name = $request->input('name') ?? $userData->name;
            $userData->phone = $request->input('phone') ??  $userData->phone;
            $userData->address = $request->input('address') ??  $userData->address;
            $userData->email = $request->input('email') ??  $userData->email;
            $userData->save();

            $message = "Your Profile Updated Successfully";
            return $this->responseSuccess(200, true, $message, $userData);
        } catch (ValidationException $e) {
            $message = $e->getMessage() ?: 'Validation failed';

            return $this->responseError(422, false, $message);
        }
    }


    //------------ Start Parent Permission  -----------
    public function parentPermissionList(Request $request)
    {
        $parentPermissionData = ParentPermission::get();

        if ($parentPermissionData->isEmpty()) {
            $message = "No data found.";
            return $this->responseError(403, false, $message);
        }

        $message = "Successfully data shown";
        return $this->responseSuccess(200, true, $message, $parentPermissionData);
    }

    public function parentPermissionStore(Request $request)
    {
        try {
            $rules = [
                'name' => 'required|string|max:255',
                'status' => 'required|boolean',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return $this->responseError(Response::HTTP_UNPROCESSABLE_ENTITY, false, $validator->errors()->first());
            }

            $data = [
                'name' => $request->name,
                'created_by' => auth()->id(),
                'status' => $request->status,
            ];

            $category = ParentPermission::create($data);

            $message = "Parent Permission Module Created Successfully";
            return $this->responseSuccess(200, true, $message, $category);
        } catch (\Exception $e) {
            \Log::error($e);
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }

}
