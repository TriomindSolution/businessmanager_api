<?php

namespace App\Http\Controllers\Backend\Expense;

use App\Models\Expense;
use App\Traits\ResponseTrait;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{

    use ResponseTrait;



    public function expenseList(Request $request)
    {
        $expenseData = Expense::where('status', 1)->latest()->get();

        // not empty checking
        if ($expenseData->isEmpty()) {
            $message = "No expense data found.";
            return $this->responseError(403, false, $message);
        }

        $message = "Successfully data shown";
        return $this->responseSuccess(200, true, $message, $expenseData);
    }





    public function expenseRetrieve($expenseId)
    {
        $expenseData = Expense::where('id', $expenseId)->get();

        // not empty checking
        if ($expenseData->isEmpty()) {
            $message = "No expense data found.";
            return $this->responseError(403, false, $message);
        }

        $message = "Successfully data shown";
        return $this->responseSuccess(200, true, $message, $expenseData);
    }






    public function expenseStore(Request $request)
    {
        try {
            $request->validate([
                'amount' => 'required|string',
                'date' => 'required|string',
                'details'=>'required|string',
                'status' => 'nullable|string',
                'expensecategory_id' => 'required|integer',
                'created_by' => 'nullable|string'
            ]);

            $data = [
                'amount' => $request->amount,
                'date' => $request->date,
                'details'=>$request->details,
                'status'=>$request->status,
                'expensecategory_id'=>$request->expensecategory_id,
                 'created_by' => auth()->id()

            ];

            $expense = Expense::create($data);

            $message = "Expense Created Successfully";
            return $this->responseSuccess(200, true, $message, $expense); // Use responseSuccess method from the ResponseTrait
        } catch (\Exception $e) {
            \Log::error($e);
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage()); // Use responseError method from the ResponseTrait
        }
    }


    public function expenseUpdate(Request $request, $id)
    {
        $expenseData =Expense::findOrFail($id);

        try {
            if ($expenseData) {
                $expenseData->update([
                    'amount' => $request->amount ?? $expenseData->amount,
                    'date' => $request->date ?? $expenseData->date,
                    'details' => $request->details ?? $expenseData->details,
                    'status' => $request->status ?? $expenseData->status,
                    'expensecategory_id'=>$request->expensecategory_id  ?? $expenseData->expensecategory

                ]);

                $message = "expense data has been updated";

                return $this->responseSuccess(200, true, $message, $expenseData);
            }
        } catch (QueryException $e) {
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }



    public function destroy($expenseId)
    {
        try {
            $expenseData = Expense::findOrFail($expenseId);
            if ( $expenseData) {
                 $expenseData->delete();
                $message = "Expense Deleted Succesfully";

                return $this->responseSuccess(200, true, $message, []);
            }
        } catch (QueryException $e) {
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }





}

