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
}
