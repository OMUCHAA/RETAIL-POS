<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\Request;
use illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $products = Product::with(['category', 'inventory'])
            ->where('status', true)
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->where('barcode', 'like', '%' . $request->search . '%')
                        ->orWhere('name', 'like', '%' . $request->search . '%')
                        ->orWhere('SKU', 'like', '%' . $request->search . '%');
                });
            })->latest()->paginate(10)->withQueryString();

        return response()->json([
            'products' => $products
        ], 200);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //Validate
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'barcode' => 'required|string|max:255|unique:products,barcode',
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp',
            'SKU' => 'string|string|max:255|unique:products,SKU',
            'description' => 'nullable|string',
            'buying_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'unit' => 'required|string|min:100',
            'minimum_stock' => 'required|integer|min:0'
        ]);

        $product = DB::transaction(function () use ($validated) {
            $product = Product::create([...$validated, 'status' => true]);
            Inventory::create([
                'product_id'=> $product->id,
                'quantity' => 0,
                'last_stock_update' => now()
            ]);
            return $product;
        });

        return response()->json([
            'product'=> $product,
            'message'=> 'Product created successfully'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load(['category', 'inventory']);
        //Using Model binding
        return response()->json([
            'product'=> $product
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        //Validate
        $validated = $request->validate([
            'category_id'=> 'required|exists:categories,id',
            'barcode'=> 'string|max:255|unique:products,barcode,' . $product->id,
            'name'=> 'required|string|max:255',
            'image'=> 'nullable|image|mimes:jpeg,jpg,png,webp',
            'SKU'=> 'required|string|unique:products,SKU,' . $product->id,
            'description'=> 'nullable|string',
            'buying_price'=> 'required|numeric|min:0',
            'selling_price'=> 'required|numeric|min:0',
            'unit'=> 'required|string|max:100',
            'minimum_stock'=> 'required|integer|min:0',
            'status'=> 'required|boolean'
        ]);

        $product->update($validated);

        return response()->json([
            'message'=> 'Product updated successfully',
            'product'=> $product
        ], 200);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        //
    }
}
