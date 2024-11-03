<?php

namespace App\Http\Controllers\RolePermission;

use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PermissionController extends Controller
{
    use ResponseTrait;

    public function permissionList(Request $request)
    {
        $permissionData = Permission::get();

        if ($permissionData->isEmpty()) {
            $message = "No data found.";
            return $this->responseError(403, false, $message);
        }

        $message = "Successfully data shown";
        return $this->responseSuccess(200, true, $message, $permissionData);
    }


    public function permissionStore(Request $request){
        try {
            $rules = [
                'name' => 'required|string|max:255',
                'slug' => 'required|string|max:255|unique:permissions,slug',
                'parent_id' => 'required|exists:parent_permissions,id',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return $this->responseError(Response::HTTP_UNPROCESSABLE_ENTITY, false, $validator->errors()->first());
            }

            $data = [
                'name' => $request->name,
                'slug' => $request->slug,
                'parent_id' => $request->parent_id,
                'created_by' => auth()->id(),
                'status' => $request->status,
            ];

            $permission = Permission::create($data);

            $message = "Permission created successfully";
            return $this->responseSuccess(200, true, $message, $permission);
        } catch (\Exception $e) {
            \Log::error($e);
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }

    public function permissionRetrieve($id)
    {
        $permission = Permission::find($id);

        if (!$permission) {
            $message = "No data found.";
            return $this->responseError(403, false, $message);
        }

        $message = "Successfully data shown";
        return $this->responseSuccess(200, true, $message, $permission);
    }


    public function permissionUpdate(Request $request, $id)
    {
        $permission = Permission::find($id);

        if (!$permission) {
            $message = "No data found.";
            return $this->responseError(403, false, $message);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:255|unique:permissions,slug,' . $id,
            'parent_id' => 'required|exists:parent_permissions,id',
        ]);

        $permission->update([
            'name' => $request->name ?? $permission->name,
            'created_by' => auth()->id(),
            'status' => $request->status ?? $permission->status,
            'parent_id' => $request->parent_id ?? $permission->parent_id,
            'slug' => $request->slug ?? $permission->slug,
        ]);

        $message = "Successfully data shown";
        return $this->responseSuccess(200, true, $message, $permission);
    }

    public function destroy($id)
    {
        $permission = Permission::find($id);

        if (!$permission) {
            $message = "No data found.";
            return $this->responseError(403, false, $message);
        }

        $permission->delete();

        $message = "Successfully Deleted data";
        return $this->responseSuccess(200, true, $message, []);
    }
}
