<?php

namespace App\Http\Controllers\RolePermission;

use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Illuminate\Http\Response;
use App\Models\ParentPermission;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;


class RoleController extends Controller
{
    use ResponseTrait;

    public function roleList(Request $request)
    {
        $roleData = Role::get();

        if ($roleData->isEmpty()) {
            $message = "No data found.";
            return $this->responseError(403, false, $message);
        }

        $message = "Successfully data shown";
        return $this->responseSuccess(200, true, $message, $roleData);
    }


    public function roleStore(Request $request)
    {
        try {
            $rules = [
                'name' => 'required|string|max:255',
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

            $role = Role::create($data);

            $message = "Role created successfully";
            return $this->responseSuccess(200, true, $message, $role);
        } catch (\Exception $e) {
            \Log::error($e);
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }

    public function roleRetrieve($id)
    {
        $role = Role::find($id);

        if (!$role) {
            $message = "No data found.";
            return $this->responseError(403, false, $message);
        }

        $message = "Successfully data shown";
        return $this->responseSuccess(200, true, $message, $role);
    }


    public function roleUpdate(Request $request, $id)
    {
        $role = Role::find($id);

        if (!$role) {
            $message = "No data found.";
            return $this->responseError(403, false, $message);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
        ]);

        $role->update([
            'name' => $request->name ?? $role->name,
            'created_by' => auth()->id(),
            'status' => $request->status ?? $role->status,
        ]);

        $message = "Successfully data shown";
        return $this->responseSuccess(200, true, $message, $role);
    }

    public function destroy($id)
    {
        $role = Role::find($id);

        if (!$role) {
            $message = "No data found.";
            return $this->responseError(403, false, $message);
        }

        $role->delete();

        $message = "Successfully Deleted data";
        return $this->responseSuccess(200, true, $message, []);
    }


    public function getParentPermissionsWithRole(Request $request, $role_id)
    {

        $role = Role::find($role_id);
        if (!$role) {
            $message = "Role not found.";
            return $this->responseError(404, false, $message);
        }


        $parentPermissions = ParentPermission::with('permissions')->get();


        $data = $parentPermissions->map(function ($parentPermission) use ($role) {
            return [
                'parent_permission_name' => $parentPermission->name,
                'permissions' => $parentPermission->permissions->map(function ($permission) use ($role) {
                    return [
                        'permission_name' => $permission->name,
                        'permission_slug' => $permission->slug,
                        'is_assigned_to_role' => $role->hasPermissionTo($permission->slug),
                    ];
                })->filter(),
            ];
        });

        $message = "Successfully data shown";
        return $this->responseSuccess(200, true, $message, $data);
    }


    public function assignPermissionsToRole(Request $request)
    {
        $rules = [
            'role_id' => 'required|exists:roles,id',
            'permissions' => 'required|array',
            'permissions.*' => 'string|exists:permissions,slug',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->responseError(Response::HTTP_UNPROCESSABLE_ENTITY, false, $validator->errors()->first());
        }

        $role = Role::find($request->role_id);
        if (!$role) {
            return $this->responseError(404, false, "Role not found.");
        }

        $permissions = Permission::whereIn('slug', $request->permissions)->get();


        $role->syncPermissions($permissions);

        return $this->responseSuccess(200, true, "Permissions successfully assigned to role.", [
            'role' => $role->name,
            'permissions_assigned' => $permissions->pluck('name'),
        ]);
    }
}
