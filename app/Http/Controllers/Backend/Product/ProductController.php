<?php

namespace App\Http\Controllers\Backend\Product;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Illuminate\Http\Response;
use App\Models\ProductVariant;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    use ResponseTrait;
    public function productList(Request $request)
    {
        $limit = $request->input('limit', 20);

        $productData = Product::with('productVariants')
            ->where('status', 1)->latest()->paginate($limit);

        if ($productData->isEmpty()) {
            $message = "No product data found.";
            return $this->responseError(403, false, $message);
        }

        $message = "Successfully data shown";
        return $this->responseSuccess(200, true, $message, $productData);
    }
    public function productRetrieve($productId)

    {
        $productData = Product::with('productVariants')->where('id', $productId)->get();

        // not empty checking
        if ($productData->isEmpty()) {
            $message = "No Product data found.";
            return $this->responseError(403, false, $message);
        }

        $message = "Successfully data shown";
        return $this->responseSuccess(200, true, $message, $productData);
    }

    public function productStore(Request $request)
    {
        // Validation rules
        $rules = [
            'category_id' => 'required',
            'seller_id' => 'nullable|exists:sellers,id',
            'name' => 'required',
            'per_unit_product_price' => 'required|numeric',
            'product_unit' => 'nullable',
            'product_quantity' => 'required|numeric',
            'total_price' => 'required|numeric',
            'stock_alert' => 'nullable|numeric',
            'product_details' => 'nullable',
            'status' => 'nullable',
            'date' => 'required',
            'product_sku_code' => 'nullable',
            'product_image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'product_document' => 'nullable|mimes:pdf,doc,docx|max:2048',
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
            $productCode = $now->format('Hisu');

            $data = [
                'category_id' => $request->category_id,
                'seller_id' => $request->seller_id,
                'name' => $request->name,
                'per_unit_product_price' => $request->per_unit_product_price,
                'product_unit' => $request->product_unit,
                'product_quantity' => $request->product_quantity,
                'total_price' => $request->total_price,
                'stock_alert' => $request->stock_alert,
                'product_details' => $request->product_details,
                'status' => $request->status,
                'date' => $request->date,
                'product_sku_code' => $request->product_sku_code,
                'product_code' => $productCode,
                'created_by' => auth()->id(),
            ];

            // Handle product image upload
            if ($request->hasFile('product_image')) {
                $image = $request->file('product_image');
                $uniqueCode = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
                $productImageName = time() . $uniqueCode . '.' . $image->getClientOriginalExtension();
                Storage::putFileAs('public/product_images', $image, $productImageName);
                $data['product_image'] = $productImageName;
            }

            // Handle product document upload
            if ($request->hasFile('product_document')) {
                $document = $request->file('product_document');
                $uniqueCode = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
                $productDocumentName = time() . $uniqueCode . '.' . $document->getClientOriginalExtension();
                Storage::putFileAs('public/product_documents', $document, $productDocumentName);
                $data['product_document'] = $productDocumentName;
            }

            $product = Product::create($data);

            if (is_array($request->variants)) {
                foreach ($request->variants as $key => $variant) {
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'size' => $request->variants[$key]['size'],
                        'color' => $request->variants[$key]['color'],
                        'quantity' => $request->variants[$key]['quantity'],
                    ]);
                }
            }

            $message = "Product Created Successfully";
            DB::commit();
            return $this->responseSuccess(200, true, $message, $product);
        } catch (QueryException $e) {
            DB::rollback();
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }


    // public function productUpdate(Request $request, $productId)
    // {
    //     // Validation rules
    //     $rules = [
    //         'category_id' => 'required',
    //         'seller_id' => 'nullable|exists:sellers,id',
    //         'name' => 'required',
    //         'per_unit_product_price' => 'required|numeric',
    //         'product_unit' => 'nullable',
    //         'product_quantity' => 'required|numeric',
    //         'total_price' => 'required|numeric',
    //         'stock_alert' => 'nullable|numeric',
    //         'product_details' => 'nullable',
    //         'status' => 'nullable',
    //         'date' => 'required',
    //         'product_sku_code' => 'nullable',
    //         'product_image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
    //         'product_document' => 'nullable|mimes:pdf,doc,docx|max:2048',
    //     ];

    //     // Validate the request
    //     $validator = Validator::make($request->all(), $rules);

    //     // Check if validation fails
    //     if ($validator->fails()) {
    //         return $this->responseError(Response::HTTP_UNPROCESSABLE_ENTITY, false, $validator->errors()->first());
    //     }

    //     DB::beginTransaction();
    //     try {
    //         $product = Product::findOrFail($productId);

    //         // Update product data
    //         $product->update([
    //             'category_id' => $request->category_id ?? $product->category_id,
    //             'seller_id' => $request->seller_id ?? $product->seller_id,
    //             'name' => $request->name ?? $product->name,
    //             'per_unit_product_price' => $request->per_unit_product_price ?? $product->per_unit_product_price,
    //             'product_unit' => $request->product_unit ?? $product->product_unit,
    //             'product_quantity' => $request->product_quantity ?? $product->product_quantity,
    //             'total_price' => $request->total_price ?? $product->total_price,
    //             'stock_alert' => $request->stock_alert ?? $product->stock_alert,
    //             'product_details' => $request->product_details ?? $product->product_details,
    //             'status' => $request->status ?? $product->status,
    //             'date' => $request->date ?? $product->date,
    //             'product_sku_code' => $request->product_sku_code ?? $product->product_sku_code,
    //             'product_code' => $product->productCode,
    //             'created_by' => auth()->id(),
    //         ]);

    //         // Handle product image update
    //         if ($request->hasFile('product_image')) {
    //             $image = $request->file('product_image');
    //             $uniqueCode = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
    //             $productImageName = time() . $uniqueCode . '.' . $image->getClientOriginalExtension();
    //             Storage::putFileAs('public/product_images', $image, $productImageName);
    //             // Delete old image if exists
    //             if ($product->product_image) {
    //                 Storage::delete('public/product_images/' . $product->product_image);
    //             }
    //             $product->update(['product_image' => $productImageName]);
    //         }

    //         // Handle product document update
    //         if ($request->hasFile('product_document')) {
    //             $document = $request->file('product_document');
    //             $uniqueCode = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
    //             $productDocumentName = time() . $uniqueCode . '.' . $document->getClientOriginalExtension();
    //             Storage::putFileAs('public/product_documents', $document, $productDocumentName);
    //             // Delete old document if exists
    //             if ($product->product_document) {
    //                 Storage::delete('public/product_documents/' . $product->product_document);
    //             }
    //             $product->update(['product_document' => $productDocumentName]);
    //         }

    //         // Handle product variants update
    //         if (is_array($request->variants)) {
    //             foreach ($request->variants as $key => $variant) {
    //                 $existId = isset($request->variants[$key]['id']);
    //                 if ($existId) {
    //                     $productVariantId = $request->variants[$key]['id'];
    //                     $productVariant = ProductVariant::where('id', $productVariantId)->first();
    //                     if ($productVariant) {
    //                         $productVariant->update([
    //                             'product_id' => $product->id,
    //                             'size' => $request->variants[$key]['size'] ?? $productVariant->size,
    //                             'color' => $request->variants[$key]['color'] ?? $productVariant->color,
    //                             'quantity' => $request->variants[$key]['quantity'] ?? $productVariant->quantity,
    //                         ]);
    //                     }
    //                 } else {
    //                     ProductVariant::create([
    //                         'product_id' => $product->id,
    //                         'size' => $request->variants[$key]['size'],
    //                         'color' => $request->variants[$key]['color'],
    //                         'quantity' => $request->variants[$key]['quantity'],
    //                     ]);
    //                 }
    //             }
    //         }

    //         $message = "Data Updated Successfully";
    //         DB::commit();
    //         return $this->responseSuccess(200, true, $message, $product);
    //     } catch (QueryException $e) {
    //         DB::rollBack();
    //     }
    // }

    public function productUpdate(Request $request, $productId)
    {
        // Validation rules
        $rules = [
            'category_id' => 'required',
            'seller_id' => 'nullable|exists:sellers,id',
            'name' => 'required',
            'per_unit_product_price' => 'required|numeric',
            'product_unit' => 'nullable',
            'product_quantity' => 'required|numeric',
            'total_price' => 'required|numeric',
            'stock_alert' => 'nullable|numeric',
            'product_details' => 'nullable',
            'status' => 'nullable',
            'date' => 'required',
            'product_sku_code' => 'nullable',
            'product_image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'product_document' => 'nullable|mimes:pdf,doc,docx|max:2048',
        ];

        // Validate the request
        $validator = Validator::make($request->all(), $rules);

        // Check if validation fails
        if ($validator->fails()) {
            return $this->responseError(Response::HTTP_UNPROCESSABLE_ENTITY, false, $validator->errors()->first());
        }

        DB::beginTransaction();
        try {
            $product = Product::findOrFail($productId);

            // Update product data
            $productData = [
                'category_id' => $request->category_id ?? $product->category_id,
                'seller_id' => $request->seller_id ?? $product->seller_id,
                'name' => $request->name ?? $product->name,
                'per_unit_product_price' => $request->per_unit_product_price ?? $product->per_unit_product_price,
                'product_unit' => $request->product_unit ?? $product->product_unit,
                'product_quantity' => $request->product_quantity ?? $product->product_quantity,
                'total_price' => $request->total_price ?? $product->total_price,
                'stock_alert' => $request->stock_alert ?? $product->stock_alert,
                'product_details' => $request->product_details ?? $product->product_details,
                'status' => $request->status ?? $product->status,
                'date' => $request->date ?? $product->date,
                'product_sku_code' => $request->product_sku_code ?? $product->product_sku_code,
                'product_code' => $product->productCode,
                'created_by' => auth()->id(),
            ];

            // Handle product image update
            if ($request->hasFile('product_image')) {
                $image = $request->file('product_image');
                $productData['product_image'] = $this->uploadProductFile($image, 'public/product_images', $product->product_image);
            }

            // Handle product document update
            if ($request->hasFile('product_document')) {
                $document = $request->file('product_document');
                $productData['product_document'] = $this->uploadProductFile($document, 'public/product_documents', $product->product_document);
            }

            $product->update($productData);

            // Handle product variants update
            if (is_array($request->variants)) {
                foreach ($request->variants as $key => $variant) {
                    $existId = isset($request->variants[$key]['id']);
                    if ($existId) {
                        $productVariantId = $request->variants[$key]['id'];
                        $productVariant = ProductVariant::where('id', $productVariantId)->first();
                        if ($productVariant) {
                            $productVariant->update([
                                'product_id' => $product->id,
                                'size' => $request->variants[$key]['size'] ?? $productVariant->size,
                                'color' => $request->variants[$key]['color'] ?? $productVariant->color,
                                'quantity' => $request->variants[$key]['quantity'] ?? $productVariant->quantity,
                            ]);
                        }
                    } else {
                        ProductVariant::create([
                            'product_id' => $product->id,
                            'size' => $request->variants[$key]['size'],
                            'color' => $request->variants[$key]['color'],
                            'quantity' => $request->variants[$key]['quantity'],
                        ]);
                    }
                }
            }

            $message = "Data Updated Successfully";
            DB::commit();
            return $this->responseSuccess(200, true, $message, $product);
        } catch (QueryException $e) {
            DB::rollBack();
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }

    private function uploadProductFile($file, $directory, $existingFile)
    {
        if ($existingFile) {
            Storage::delete($directory . '/' . $existingFile);
        }

        $uniqueCode = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        $fileName = time() . $uniqueCode . '.' . $file->getClientOriginalExtension();
        Storage::putFileAs($directory, $file, $fileName);

        return $fileName;
    }



    public function productVariantDestroy($productVariantId)
    {
        DB::beginTransaction();
        try {
            $productVariant = ProductVariant::where('id', $productVariantId)->first();
            if ($productVariant != null) {
                $productVariant->delete();
                $message = "Product Variant Deleted Successfully";
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
