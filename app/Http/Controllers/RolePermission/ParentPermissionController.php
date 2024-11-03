<?php

namespace App\Http\Controllers\RolePermission;

use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Illuminate\Http\Response;
use App\Models\ParentPermission;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ParentPermissionController extends Controller
{
    use ResponseTrait;
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

    public function parentPermissionRetrieve($parentPermissionId)
    {
        $parentPermissionData = ParentPermission::where('id', $parentPermissionId)->get();

        // not empty checking
        if (!$parentPermissionData) {
            $message = "No parent permission data found.";
            return $this->responseError(403, false, $message);
        }

        $message = "Successfully data shown";
        return $this->responseSuccess(200, true, $message, $parentPermissionData);
    }

    public function parentPermissionUpdate(Request $request, $id)
    {
        try {
            $parentPermissionData = ParentPermission::findOrFail($id);
            $parentPermissionData->update([
                'name' => $request->name ?? $parentPermissionData->name,
                'created_by' => auth()->id(),
                'status' => $request->status ?? $parentPermissionData->status,
            ]);

            $message = "Parent Permission data has been updated";

            return $this->responseSuccess(200, true, $message, $parentPermissionData);
        } catch (ModelNotFoundException $e) {
            $message = "Parent Permission id not found.";
            return $this->responseError(Response::HTTP_NOT_FOUND, false, $message);
        }
    }



    public function destroy($id)
    {
        $parentPermission = ParentPermission::find($id);

        if (!$parentPermission) {
            $message = "No Parent Permission data found.";
            return $this->responseError(403, false, $message);
        }

        // if ($parentPermission->products()->count() > 0) {
        //     $message = "Cannot delete category because it has products associated with it.";
        //     return $this->responseError(403, false, $message);
        // }

        $parentPermission->delete();

        $message = "Parent Permission deleted successfully";
        return $this->responseSuccess(200, true, $message,[]);
    }
}
