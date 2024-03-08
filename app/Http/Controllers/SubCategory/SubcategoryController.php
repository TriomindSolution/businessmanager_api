<?php

namespace App\Http\Controllers\SubCategory;

use App\Traits\ResponseTrait;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Models\Subcategory;
class SubcategoryController extends Controller
{
    public function subcategoryStore(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|unique:subcategories',
                'status' => 'nullable|string',
                 'category_id'=>'required|string',
            ]);

            $data = [
                'name' => $request->name,
                'status' => $request->status, 
                'category_id' => $request->category_id, 
            ];

            $subcategory = Subcategory::create($data);

            $message = "Subcategory Created Successfully";
            return $this->responseSuccess(200, true, $message, $subcategory); 
        } catch (\Exception $e) {
            \Log::error($e);
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage()); 
        }
    }


    public function subcategoryUpdate(Request $request, $id)
    {
        $subcategoryData =Subcategory::findOrFail($id);

        try {
            if ($subcategoryData) {
                $subcategoryData->update([
                    'name' => $request->name ?? $subcategoryData->name,
                    'status' => $request->status ?? $subcategoryData->status,
                    'category_id' => $request->category_id ?? $subcategoryData->category_id,
                    
                ]);

                $message = "Subcategory data has been updated";

                return $this->responseSuccess(200, true, $message, $subcategoryData);
            }
        } catch (QueryException $e) {
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }


    public function destroy($subcategoryId)
    {
        try {
            $subcategoryData = Subcategory::findOrFail($subcategoryId);
            if ( $subcategoryData) {
                 $subcategoryData->delete();
                $message = "SubCategory Deleted Succesfully";

                return $this->responseSuccess(200, true, $message, []);
            }
        } catch (QueryException $e) {
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }







}
