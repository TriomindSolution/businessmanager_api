<?php

namespace App\Http\Controllers\Backend\Categories;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;


class CategoriesController extends Controller
{
    use ResponseTrait;
    public function categoryStore(Request $request)
    {
        try {
            $rules = [
                'category_name' => 'required|string|max:255',
                'parent_id' => 'nullable|exists:categories,id',
                'status' => 'required|boolean',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return $this->responseError(Response::HTTP_UNPROCESSABLE_ENTITY, false, $validator->errors()->first());
            }

            $data = [
                'category_name' => $request->category_name,
                'created_by' => auth()->id(),
                'parent_id' => $request->parent_id,
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
        $limit = $request->input('limit', 20);

        $categoryData = Category::with('parent', 'children')->where('status', 1)->latest()->paginate($limit);

        if ($categoryData->isEmpty()) {
            $message = "No category data found.";
            return $this->responseError(403, false, $message);
        }

        $message = "Successfully data shown";
        return $this->responseSuccess(200, true, $message, $categoryData);
    }


    public function categoryRetrieve($categoryId)
    {
        $categoryData = Category::with('parent', 'children')->where('id', $categoryId)->get();

        // not empty checking
        if ($categoryData->isEmpty()) {
            $message = "No category data found.";
            return $this->responseError(403, false, $message);
        }

        $message = "Successfully data shown";
        return $this->responseSuccess(200, true, $message, $categoryData);
    }

    public function categoryUpdate(Request $request, $id)
    {
        $categoryData = Category::with('parent','children')->findOrFail($id);

        try {
            if ($categoryData) {
                $categoryData->update([
                    'category_name' => $request->category_name ?? $categoryData->category_name,
                    'parent_id' => $request->parent_id ?? $categoryData->parent_id,
                    'created_by' => auth()->id(),
                    'status' => $request->status ?? $categoryData->status,
                ]);

                $message = "Category data has been updated";

                return $this->responseSuccess(200, true, $message, $categoryData);
            }
        } catch (QueryException $e) {
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }
}
