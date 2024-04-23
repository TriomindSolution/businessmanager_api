<?php

namespace App\Http\Controllers\Backend\Seller;

use App\Models\Seller;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Requests\SellerRequest;
use Illuminate\Database\QueryException;

class SellerController extends Controller
{
    use ResponseTrait;

    public function sellerList(Request $request)
    {
        $sellerData = Seller::where('status', 1)->latest()->get();

        // not empty checking
        if ($sellerData->isEmpty()) {
            $message = "No seller data found.";
            return $this->responseError(403, false, $message);
        }

        $message = "Successfully data shown";
        return $this->responseSuccess(200, true, $message, $sellerData);
    }


    public function sellerRetrieve($sellerId)
    {
        $sellerData = Seller::where('id', $sellerId)->get();

        // not empty checking
        if ($sellerData->isEmpty()) {
            $message = "No seller data found.";
            return $this->responseError(403, false, $message);
        }

        $message = "Successfully data shown";
        return $this->responseSuccess(200, true, $message, $sellerData);
    }


    public function sellerStore(SellerRequest $request)
    {
        try {
            $data = [
                'name' => $request->name,
                'address' => $request->address,
                'phone' => $request->phone,
                'email' => $request->email,
                'created_by' => auth()->id(),
                'description' => $request->description,
                'status' => $request->status,
            ];

            $product = Seller::create($data);

            $message = "Seller created successfully";
            return $this->responseSuccess(200, true, $message, $product);
        } catch (\Exception $e) {

            \Log::error($e);

            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }

    public function sellerUpdate(SellerRequest $request, $id)
    {
        $sellerData = Seller::findOrFail($id);

        try {
            if ($sellerData) {
                $sellerData->update([
                    'name' => $request->name ?? $sellerData->name,
                    'address' => $request->address ?? $sellerData->address,
                    'phone' => $request->phone ?? $sellerData->phone,
                    'email' => $request->email ?? $sellerData->email,
                    'created_by' => auth()->id(),
                    'description' => $request->description ?? $sellerData->description,
                    'status' => $request->status ?? $sellerData->status,
                ]);

                $message = "Seller data has been updated";

                return $this->responseSuccess(200, true, $message, $sellerData);
            }
        } catch (QueryException $e) {
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }


    public function destroy($sellerId)
    {
        try {
            $sellerData = Seller::findOrFail($sellerId);
            if ($sellerData) {
                $sellerData->delete();
                $message = "Seller deleted succesfully";

                return $this->responseSuccess(200, true, $message, []);
            }
        } catch (QueryException $e) {
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }
}
