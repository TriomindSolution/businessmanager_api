<?php

namespace App\Http\Controllers\Backend\Customer;

use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use App\Models\Customer;


class CustomerController extends Controller
{

    use ResponseTrait;



    public function customerList(Request $request)
    {
        $limit = $request->input('limit', 20);

        $cusData = Expense::latest()->paginate($limit);

        // not empty checking
        if ($cusData->isEmpty()) {
            $message = "No customer data found.";
            return $this->responseError(403, false, $message);
        }

        $message = "Successfully data shown";
        return $this->responseSuccess(200, true, $message, $cusData);
    }





    public function cusRetrieve($expenseId)
    {
        $cusData = Customer::where('id', $expenseId)->get();

        // not empty checking
        if ($cusData->isEmpty()) {
            $message = "No Customer data found.";
            return $this->responseError(403, false, $message);
        }

        $message = "Successfully data shown";
        return $this->responseSuccess(200, true, $message, $cusData);
    }





    public function customerStore(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string',
                'phone' => 'required|string',
                'address_1'=>'required|string',
                'address_2' => 'nullable|string',
                'customer_code' => 'nullable|integer',
                'order_count' => 'nullable|string',
                'created_by' => 'nullable|string'


            ]);

            $data = [
                'name' => $request->name,
                'phone' => $request->phone,
                'address_1'=>$request->address_1,
                'address_2'=>$request->address_2,
                'customer_code'=>mt_rand(100000, 999999),
                'order_count'=>$request->order_count,
                'created_by' => auth()->id()

            ];

            $customer = Customer::create($data);

            $message = "Customer Created Successfully";
            return $this->responseSuccess(200, true, $message, $customer); // Use responseSuccess method from the ResponseTrait
        } catch (\Exception $e) {
            \Log::error($e);
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage()); // Use responseError method from the ResponseTrait
        }
    }




    public function customerUpdate(Request $request, $id)
    {
        $customerData =Customer::findOrFail($id);

        try {
            if ($customerData) {
                $customerData->update([

                    'name' => $request->name ?? $customerData->name,
                    'phone' => $request->phone ??   $customerData->phone,
                    'address_1'=>$request->address_1 ?? $customerData->address_1,
                    'address_2'=>$request->address_2 ??   $customerData->address_2 ,




                ]);

                $message = "customer data has been updated";

                return $this->responseSuccess(200, true, $message, $customerData);
            }
        } catch (QueryException $e) {
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }




    public function destroy($customerId)
    {
        try {
            $customerData = Customer::findOrFail($customerId);
            if ( $customerData) {
                $customerData->delete();
                $message = "Customer Deleted Succesfully";

                return $this->responseSuccess(200, true, $message, []);
            }
        } catch (QueryException $e) {
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }
























}
