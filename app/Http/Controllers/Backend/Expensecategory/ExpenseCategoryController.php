<?php

namespace App\Http\Controllers\Backend\ExpenseCategory;

use App\Models\ExpenseCategory;
use App\Traits\ResponseTrait;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\Response;
use Illuminate\Http\Request;


class ExpenseCategoryController extends Controller
{
    use ResponseTrait;
    public function expenseCatgoeryList(Request $request)
    {
        $expenseCategoryData = ExpenseCategory::latest()->get();;

        // not empty checking
        if ($expenseCategoryData ->isEmpty()) {
            $message = "No expenseCategory data found.";
            return $this->responseError(403, false, $message);
        }

        $message = "Successfully data shown";
        return $this->responseSuccess(200, true, $message,  $expenseCategoryData );
    }


    public function expenseCategoryRetrieve($expenseCategoryId)
    {
        $expensecategoryData = ExpenseCategory::where('id', $expenseCategoryId)->get();

        // not empty checking
        if ($expensecategoryData->isEmpty()) {
            $message = "No expensecategory data found.";
            return $this->responseError(403, false, $message);
        }

        $message = "Successfully data shown";
        return $this->responseSuccess(200, true, $message,    $expensecategoryData);
    }


    public function expensecategoryStore(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|unique:expense_categories',
                'status' => 'nullable|string',
                'created_by' => 'nullable|string'
            ]);

            $data = [
                'name' => $request->name,
                'status' => $request->status,
                'created_by' => auth()->id()
            ];

            $expensecategory = ExpenseCategory::create($data);

            $message = "Expensecategory Created Successfully";
            return $this->responseSuccess(200, true, $message, $expensecategory); // Use responseSuccess method from the ResponseTrait
        } catch (\Exception $e) {
            \Log::error($e);
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage()); // Use responseError method from the ResponseTrait
        }
    }


    public function expensecategoryUpdate(Request $request, $id)
    {
        $expensecategoryData =ExpenseCategory::findOrFail($id);

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
