<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Inventory;
use App\Models\Purchase_Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $purchases = Purchase::with('supplier')
        ->when($request->search, function ($query) use ($request)  {
            $query->where('invoice_number', 'like', '%' . $request->search . '%')
            ->orWhereHas('supplier', function($query) use ($request) {
                $query->where('supplier_name', 'like', '%' . $request->search . '%');
            });
        })->latest()->paginate(10)->withQueryString();

        return response()->json([
            'purchases' => $purchases
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //Validation
        $validated =$request->validate([
            'supplier_id'=> 'required|exists:suppliers,id',
            'purchase_date'=> 'required|date',
            'invoice_number'=> 'required|string|max:255|unique:purchases,invoice_number',
            'payment_status'=> 'required|in:pending,partial,paid',
            'remaks'=> 'nullable|string',
            //items data
            'items'=> 'required|array|min:1',
            'items.*.product_id'=> 'required|exists:products,id',
            'items.*.quantity'=> 'required|integer|min:1',
            'items.*.buying_price'=> 'required|integer|min:0'
        ]);

        $purchase = DB::transaction(function() use ($validated) {
            //Getting all the validated purchase records.
            $purchase = Purchase::create([
                'supplier_id'=> $validated['supplier_id'],
                'purchase_date'=> $validated['purchase_date'],
                'invoice_number'=> $validated['invoice_number'],
                'payment_status'=> $validated['payment_status'],
                'remarks'=> $validated['remarks'] ?? null,
                'total_amount'=> 0
            ]);

            //Going through all the items and calculating and adding all the subtotal.          
            $totalAmount = 0;
            foreach($validated['items'] as $item) {
                $subtotal = $item['quantity'] * $item['buying_price'];
                Purchase_Item::create([
                    'purchase_id'=> $purchase->id,
                    'product_id'=> $item['product_id'],
                    'quantity'=> $item['quantity'],
                    'buying_price'=> $item['buying_price'],
                    'subtotal'=> $subtotal
                ]);

                $totalAmount += $subtotal;

                //Update the Inventory
                $inventory = Inventory::where('product_id', $item['product_id'])->first();
                $inventory->quantity += $item['quantity'];
                $inventory->save();
            }
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Purchase $purchase)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Purchase $purchase)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Purchase $purchase)
    {
        //
    }
}
