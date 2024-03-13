<?php

namespace App\Http\Controllers\Backend\Expensecategory;

use App\Models\Expensecategory;
use App\Traits\ResponseTrait;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Models\Expensecategorycategory;

class ExpensecategoryController extends Controller
{
    use ResponseTrait;

    public function expensecategoryStore(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|unique:expensecategories',
                'status' => 'nullable|string',
            ]);

            $data = [
                'name' => $request->name,
                'status' => $request->status, // Use $request->status instead of $request->name
            ];

            $expensecategory = Expensecategory::create($data);

            $message = "Expensecategory Created Successfully";
            return $this->responseSuccess(200, true, $message, $expensecategory); // Use responseSuccess method from the ResponseTrait
        } catch (\Exception $e) {
            \Log::error($e);
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage()); // Use responseError method from the ResponseTrait
        }
    }


    public function expensecategoryUpdate(Request $request, $id)
    {
        $expensecategoryData =Expensecategory::findOrFail($id);

        try {
            if ($expensecategoryData) {
                $expensecategoryData->update([
                    'name' => $request->name ?? $expensecategoryData->name,
                    'status' => $request->status ?? $expensecategoryData->status,
                    
                ]);

                $message = "expensecategory data has been updated";

                return $this->responseSuccess(200, true, $message, $expensecategoryData);
            }
        } catch (QueryException $e) {
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }



    public function destroy($expensecategoryId)
    {
        try {
            $expensecategoryData = Expensecategory::findOrFail($expensecategoryId);
            if ( $expensecategoryData) {
                 $expensecategoryData->delete();
                $message = "Expensecategory Deleted Succesfully";

                return $this->responseSuccess(200, true, $message, []);
            }
        } catch (QueryException $e) {
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }








    
}
