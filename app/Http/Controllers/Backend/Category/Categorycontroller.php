<?php

namespace App\Http\Controllers\Backend\Category;

use App\Traits\ResponseTrait;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    use ResponseTrait; // Import the ResponseTrait

    public function categoryStore(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|unique:categories',
                'status' => 'nullable|string',
            ]);

            $data = [
                'name' => $request->name,
                'status' => $request->status, // Use $request->status instead of $request->name
            ];

            $category = Category::create($data);

            $message = "Category Created Successfully";
            return $this->responseSuccess(200, true, $message, $category); // Use responseSuccess method from the ResponseTrait
        } catch (\Exception $e) {
            \Log::error($e);
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage()); // Use responseError method from the ResponseTrait
        }
    }





public function categoryUpdate(Request $request, $id)
    {
        $categoryData =Category::findOrFail($id);

        try {
            if ($categoryData) {
                $categoryData->update([
                    'name' => $request->name ?? $categoryData->name,
                    'status' => $request->status ?? $categoryData->status,
                    
                ]);

                $message = "category data has been updated";

                return $this->responseSuccess(200, true, $message, $categoryData);
            }
        } catch (QueryException $e) {
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }



public function destroy($categoryId)
    {
        try {
            $categoryData = Category::findOrFail($categoryId);
            if ( $categoryData) {
                 $categoryData->delete();
                $message = "Category Deleted Succesfully";

                return $this->responseSuccess(200, true, $message, []);
            }
        } catch (QueryException $e) {
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }









}
