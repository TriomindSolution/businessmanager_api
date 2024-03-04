<?php

namespace App\Http\Controllers\Backend\Product;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    use ResponseTrait;
    public function productStore(Request $request)
    {
        try {
            $request->validate([
                'category_id' => 'required',
                'buyer_id' => 'required|string',
                'color_id' => 'nullable|string',
                'name_bn' => 'nullable',
                'name_en' => 'required',
                'quantity' => 'required|string',
                'price' => 'required|string',
                'description' => 'nullable|string',
                'status' => 'nullable|string',
                'date' => 'required|string',
            ]);

            $productCode = mt_rand(100000000, 999999999);

            $data = [
                'category_id' => $request->category_id,
                'buyer_id' => $request->buyer_id,
                'color_id' => $request->color_id,
                'status' => $request->status,
                'created_by' => auth()->id(),
                'name_bn' => $request->name_bn,
                'name_en' => $request->name_en,
                'quantity' => $request->quantity,
                'price' => $request->price,
                'description' => $request->description,
                'date' => $request->date,
                'product_code' => $productCode
            ];

            $product = Product::create($data);

            $message = "Product Created Successfully";
            return $this->responseSuccess(200, true, $message, $product);
        } catch (\Exception $e) {

            \Log::error($e);

            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }
}
