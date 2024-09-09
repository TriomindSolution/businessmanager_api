<?php

namespace App\Http\Controllers\Backend\Order;

use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use App\Models\OrderProduct;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Illuminate\Http\Response;
use App\Models\ProductVariant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{

    use ResponseTrait;

    public function orderList(Request $request)
    {
        $limit = $request->input('limit', 20);

        $productData = Order::with('orderVariants', 'orderCustomer')
            ->latest()->paginate($limit);

        if ($productData->isEmpty()) {
            $message = "No order data found.";
            return $this->responseError(403, false, $message);
        }

        $message = "Successfully data shown";
        return $this->responseSuccess(200, true, $message, $productData);
    }

    public function orderRetrieve($orderId)

    {
        $orderData = Order::with('orderVariants', 'orderCustomer')->where('id', $orderId)->get();

        // not empty checking
        if ($orderData->isEmpty()) {
            $message = "No order data found.";
            return $this->responseError(403, false, $message);
        }

        $message = "Successfully data shown";
        return $this->responseSuccess(200, true, $message, $orderData);
    }

    public function orderStore(Request $request)
    {
        // Validation rules
        $rules = [
            'invoice_no' => 'required',
            'invoice_date' => 'nullable',
            'delivery_date' => 'nullable',
            'notes' => 'nullable|string',
            'payment' => 'nullable',
            'payment_method' => 'nullable',
            'payment_from' => 'nullable',
            'shipping_charge' => 'nullable',
            'total_amount' => 'nullable',
            'order_code' => 'nullable|string',
            'name' => 'required|string',
            'phone' => 'required|string',
            'address_1' => 'required|string',
            'address_2' => 'nullable|string',
            'productVariants' => 'required|array',
            'productVariants.*.product_id' => 'required',
            'productVariants.*.quantity' => 'required',
            'productVariants.*.price' => 'required',
            'productVariants.*.discount' => 'nullable',
            'productVariants.*.tax' => 'nullable',
            'productVariants.*.product_total' => 'required|numeric',
        ];

        // Validate the request
        $validator = Validator::make($request->all(), $rules);

        // Check if validation fails
        if ($validator->fails()) {
            return $this->responseError(Response::HTTP_UNPROCESSABLE_ENTITY, false, $validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $now = Carbon::now();
            $orderCode = $now->format('Hisu');

            $data = [
                'invoice_no' => $request->invoice_no,
                'invoice_date' => $request->invoice_date,
                'delivery_date' => $request->delivery_date,
                'notes' => $request->notes,
                'payment' => $request->payment,
                'payment_method' => $request->payment_method,
                'payment_from' => $request->payment_from,
                'shipping_charge' => $request->shipping_charge,
                'total_amount' => $request->total_amount,
                'order_code' => $orderCode,
                'created_by' => auth()->id(),
            ];

            $orderData = Order::create($data);

            // Check if customer exists or create a new one
            $customer = Customer::where('phone', $request->phone)->first();
          
            if (!$customer) {
                $prefix = "CUS";
                $rand_no = mt_rand(1000000, 9999999);
                $randomNumber = $prefix . $rand_no;

                $customerData = [
                    'name' => $request->name,
                    'order_id' => $orderData->id,
                    'phone' => $request->phone,
                    'address_1' => $request->address_1,
                    'address_2' => $request->address_2,
                    'customer_code' => $randomNumber,
                    'created_by' => auth()->id(),
                    'order_count' => 1 // Initial order count for new customer
                ];

                $customer = Customer::create($customerData);
            } else {
                // Increment the order count for existing customer
                $customer->increment('order_count');
            }

            foreach ($request->productVariants as $variant) {
                // Create order product entry
                OrderProduct::create([
                    'order_id' => $orderData->id,
                    'product_id' => $variant['product_id'],
                    'quantity' => $variant['quantity'],
                    'price' => $variant['price'],
                    'discount' => $variant['discount'] ?? 0,
                    'tax' => $variant['tax'] ?? 0,
                    'product_total' => $variant['product_total'],
                ]);

                // Update product quantity
                $product = Product::findOrFail($variant['product_id']);
                $product->product_quantity -= $variant['quantity'];
                $product->save();

                // Update product variant quantity if exists
                $productVariant = ProductVariant::where('product_id', $variant['product_id'])
                    ->where('size', $variant['size'] ?? null)
                    ->where('color', $variant['color'] ?? null)
                    ->first();

                if ($productVariant) {
                    $productVariant->quantity -= $variant['quantity'];
                    $productVariant->save();
                }
            }

            $message = "Order Created Successfully";
            DB::commit();
            return $this->responseSuccess(200, true, $message, $orderData);
        } catch (QueryException $e) {
            DB::rollback();
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }



    public function orderUpdate(Request $request, $orderId)
    {
        // Validation rules
        $rules = [
            'invoice_no' => 'required',
            'invoice_date' => 'nullable',
            'delivery_date' => 'nullable',
            'notes' => 'nullable|string',
            'payment' => 'nullable',
            'payment_method' => 'nullable',
            'payment_from' => 'nullable',
            'shipping_charge' => 'nullable',
            'total_amount' => 'nullable',
            'order_code' => 'nullable|string',
            'name' => 'required|string',
            'phone' => 'required|string',
            'address_1' => 'required|string',
            'address_2' => 'nullable|string',
            'variants' => 'required|array',
            'productVariants.*.product_id' => 'required',
            'productVariants.*.quantity' => 'required',
            'productVariants.*.price' => 'required',
            'productVariants.*.discount' => 'nullable',
            'productVariants.*.tax' => 'nullable',
            'productVariants.*.product_total' => 'required|numeric',
        ];

        // Validate the request
        $validator = Validator::make($request->all(), $rules);

        // Check if validation fails
        if ($validator->fails()) {
            return $this->responseError(Response::HTTP_UNPROCESSABLE_ENTITY, false, $validator->errors()->first());
        }

        DB::beginTransaction();
        try {

            $order = Order::findOrFail($orderId);

            // Update order data
            $orderData = [
                'invoice_no' => $request->invoice_no ?? $order->invoice_no,
                'invoice_date' => $request->invoice_date ?? $order->invoice_date,
                'delivery_date' => $request->delivery_date ?? $order->delivery_date,
                'notes' => $request->notes ?? $order->notes,
                'payment' => $request->payment ?? $order->payment,
                'payment_method' => $request->payment_method ?? $order->payment_method,
                'payment_from' => $request->payment_from ?? $order->payment_from,
                'shipping_charge' => $request->shipping_charge ?? $order->shipping_charge,
                'total_amount' => $request->total_amount ?? $order->total_amount,
                'created_by' => auth()->id() ?? $order->created_by,
            ];

            $order->update($orderData);

            $customerData = Customer::where('phone', $request->phone)->first();
            // dd($customerData);
            $customerUpdateData = [
                'name' => $request->name ?? $customerData->name,
                // 'order_id' => $order->id,
                'phone' => $request->phone ?? $customerData->phone,
                'address_1' => $request->address_1 ?? $customerData->address_1,

                'created_by' => auth()->id() ?? $customerData->created_by,
            ];

            $customerData->update($customerUpdateData);

            if (is_array($request->variants)) {
                foreach ($request->variants as $key => $variant) {
                    $existId = isset($request->variants[$key]['id']);
                    if ($existId) {
                        $orderVariantId = $request->variants[$key]['id'];
                        $orderVariant = OrderProduct::where('id', $orderVariantId)->first();
                        if ($orderVariant) {
                            $orderVariant->update([
                                'order_id' => $orderVariant->order_id,
                                'product_id' => $request->variants[$key]['product_id'] ?? $orderVariant->product_id,
                                'quantity' => $request->variants[$key]['quantity'] ?? $orderVariant->quantity,
                                'price' => $request->variants[$key]['price'] ?? $orderVariant->price,
                                'discount' => $request->variants[$key]['discount'] ?? $orderVariant->discount,
                                'tax' => $request->variants[$key]['tax'] ?? $orderVariant->tax,
                                'product_total' => $request->variants[$key]['product_total'] ?? $orderVariant->product_total,
                            ]);
                            // dd($orderVariant->product_id);
                            // Update product quantity
                            $product = Product::findOrFail($variant['product_id']);

                            $product->product_quantity -= $variant['quantity'];
                            $product->save();

                            // Update product variant quantity if exists
                            $productVariant = ProductVariant::where('product_id', $variant['product_id'])
                                ->where('size', $variant['size'] ?? null)
                                ->where('color', $variant['color'] ?? null)
                                ->first();

                            if ($productVariant) {
                                $productVariant->quantity -= $variant['quantity'];
                                $productVariant->save();
                            }
                        }
                    } else {
                        OrderProduct::create([
                            'order_id' => $order->id,
                            'product_id' => $variant['product_id'],
                            'quantity' => $variant['quantity'],
                            'price' => $variant['price'],
                            'discount' => $variant['discount'] ?? 0,
                            'tax' => $variant['tax'] ?? 0,
                            'product_total' => $variant['product_total'],
                        ]);

                        // Update product quantity
                        $product = Product::findOrFail($variant['product_id']);

                        $product->product_quantity -= $variant['quantity'];
                        $product->save();

                        // Update product variant quantity if exists
                        $productVariant = ProductVariant::where('product_id', $variant['product_id'])
                            ->where('size', $variant['size'] ?? null)
                            ->where('color', $variant['color'] ?? null)
                            ->first();

                        if ($productVariant) {
                            $productVariant->quantity -= $variant['quantity'];
                            $productVariant->save();
                        }
                    }
                }
            }

            $message = "Order Updated Successfully";
            DB::commit();
            return $this->responseSuccess(200, true, $message, $orderData);
        } catch (QueryException $e) {
            DB::rollback();
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }

    public function orderVariantDestroy($orderVariantId)
    {
        DB::beginTransaction();
        try {
            $orderVariant = OrderProduct::where('id', $orderVariantId)->first();
            if ($orderVariant != null) {
                $orderVariant->delete();
                $message = "Order Variant Deleted Successfully";
                DB::commit();
                return $this->responseSuccess(200, true, $message, []);
            } else {
                $message = "No Data Found";
                return $this->responseError(404, false, $message);
            }
        } catch (QueryException $e) {
            DB::rollBack();
        }
    }
}
