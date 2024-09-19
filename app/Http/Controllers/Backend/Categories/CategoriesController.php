<?php

namespace App\Http\Controllers\Backend\Categories;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CategoriesController extends Controller
{
    use ResponseTrait;
    public function categoryStore(Request $request)
    {
        try {
            $rules = [
                'category_name' => 'required|string|max:255',
                'status' => 'required|boolean',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return $this->responseError(Response::HTTP_UNPROCESSABLE_ENTITY, false, $validator->errors()->first());
            }

            $data = [
                'category_name' => $request->category_name,
                'created_by' => auth()->id(),
                'status' => $request->status,
            ];

            $category = Category::create($data);

            $message = "Category created successfully";
            return $this->responseSuccess(200, true, $message, $category);
        } catch (\Exception $e) {
            \Log::error($e);
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }


    public function categoryList(Request $request)
    {

        $categoryData = Category::latest()->get();

        if ($categoryData->isEmpty()) {
            $message = "No category data found.";
            return $this->responseError(403, false, $message);
        }

        $message = "Successfully data shown";
        return $this->responseSuccess(200, true, $message, $categoryData);
    }


    public function categoryRetrieve($categoryId)
    {
        $categoryData = Category::where('id', $categoryId)->get();

        // not empty checking
        if (!$categoryData) {
            $message = "No category data found.";
            return $this->responseError(403, false, $message);
        }

        $message = "Successfully data shown";
        return $this->responseSuccess(200, true, $message, $categoryData);
    }

    public function categoryUpdate(Request $request, $id)
    {
        try {
            $categoryData = Category::findOrFail($id);

            $categoryData->update([
                'category_name' => $request->category_name ?? $categoryData->category_name,
                'created_by' => auth()->id(),
                'status' => $request->status ?? $categoryData->status,
            ]);

            $message = "Category data has been updated";

            return $this->responseSuccess(200, true, $message, $categoryData);
        } catch (ModelNotFoundException $e) {
            $message = "Category id not found.";
            return $this->responseError(Response::HTTP_NOT_FOUND, false, $message);
        } catch (QueryException $e) {
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }


    public function destroy($id)
    {
        $category = Category::find($id);

        if (!$category) {
            $message = "No category data found.";
            return $this->responseError(403, false, $message);
        }

        if ($category->products()->count() > 0) {
            $message = "Cannot delete category because it has products associated with it.";
            return $this->responseError(403, false, $message);
        }


        $category->delete();

        $message = "Category deleted successfully";
        return $this->responseSuccess(200, true, $message,[]);
    }
}
