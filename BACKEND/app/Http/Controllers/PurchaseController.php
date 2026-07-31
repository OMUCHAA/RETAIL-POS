<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Purchase_Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PurchaseController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $purchases = Purchase::with('supplier')
      ->when($request->search, function ($query) use ($request) {
        $query->where('invoice_number', 'like', '%' . $request->search . '%')
          ->orWhereHas('supplier', function ($query) use ($request) {
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
    $validated = $request->validate([
      'supplier_id' => 'required|exists:suppliers,id',
      'purchase_date' => 'required|date',
      'invoice_number' => 'required|string|max:255|unique:purchases,invoice_number',
      'payment_status' => 'required|in:pending,partial,paid',
      'remaks' => 'nullable|string',
      //items data
      'purchaseItems' => 'required|array|min:1',
      'purchaseItems.*.product_id' => 'required|exists:products,id',
      'purchaseItems.*.quantity' => 'required|integer|min:1',
      'purchaseItems.*.buying_price' => 'required|integer|min:0'
    ]);

    $purchase = DB::transaction(function () use ($validated) {
      //Getting all the validated purchase records.
      $purchase = Purchase::create([
        'supplier_id' => $validated['supplier_id'],
        'purchase_date' => $validated['purchase_date'],
        'invoice_number' => $validated['invoice_number'],
        'payment_status' => $validated['payment_status'],
        'remarks' => $validated['remarks'] ?? null,
        'total_amount' => 0
      ]);

      //Going through all the items and calculating and adding all the subtotal.          
      $totalAmount = 0;
      foreach ($validated['purchaseItems'] as $purchaseItem) {
        $subtotal = $purchaseItem['quantity'] * $purchaseItem['buying_price'];
        Purchase_Item::create([
          'purchase_id' => $purchase->id,
          'product_id' => $purchaseItem['product_id'],
          'quantity' => $purchaseItem['quantity'],
          'buying_price' => $purchaseItem['buying_price'],
          'subtotal' => $subtotal
        ]);

        $totalAmount += $subtotal;

        //Update the Inventory
        $inventory = Inventory::where('product_id', $purchaseItem['product_id'])->first();
        $inventory->quantity += $purchaseItem['quantity'];
        $inventory->save();

        //Updating the latest product's buying price.
        $product = Product::where('id', $purchaseItem['product_id'])->first();
        $product->buying_price = $purchaseItem['buying_price'];
        $product->save();
      }

      //Update the Purchase total amount.
      $purchase->total_amount = $totalAmount;
      $purchase->save();

      return $purchase;
    });

    return response()->json([
      'message' => 'Purchase recorded successfully',
      'purchase' => $purchase->load('supplier', 'purchaseItems.product')
    ], 201);
  }

  /**
   * Display the specified resource.
   */
  public function show(Purchase $purchase)
  {
    //Load the purchase with everything related to it.
    $purchase->load('supplier', 'purchaseItems.product');

    response()->json([
      'purchase' => $purchase
    ], 200);
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, Purchase $purchase)
  {
    //Validation
    $validated = $request->validate([
      'supplier_id' => 'required|exists:suppliers,id',
      'purchase_date' => 'required|date',
      'invoice_number' => ['required', 'string', 'max:255', Rule::unique('purchases')->ignore($purchase->id)],
      'payment_status' => 'required|in:pending,partial,paid',
      'remarks' => 'nullable|string',

      'purchaseItems' => 'required|array|min:1',
      'purchaseItems.*.product_id' => 'required|exists:product,id',
      'purchaseItems.*.quantity' => 'required|integer|min:1',
      'purchaseItems.*.buying_price' => 'required|numeric|min:0',
    ]);

    //Transaction method
    $purchase = DB::transaction(function () use ($validated, $purchase) {
      // Load old purchase items.
      $purchase->load('purchaseItems');

      //Reverse inventory
      foreach ($purchase->purchaseItems as $purchaseItem) {
        $inventory = Inventory::where(
          'product_id',
          $purchaseItem->product_id
        );

        $inventory->quantity -= $purchaseItem->quantity;
        $inventory->last_stock_update = now();
        $inventory->save();
      }

      //Remove old purchase items
      $purchase->purchaseItems()->delete();

      // Update purchase header
      $purchase->update([
        'supplier_id' => $validated['supplier_id'],
        'purchase_date' => $validated['purchase_date'],
        'invoice_number' => $validated['invoice_number'],
        'payment_status' => $validated['payment_status'],
        'remarks' => $validated['remarks'] ?? null,
      ]);

      $totalAmount = 0;

      // Create new purchase items and update inventory
      foreach ($validated['purchaseItems'] as $purchaseItem) {

        $subtotal = $purchaseItem['quantity'] * $purchaseItem['buying_price'];

        Purchase_Item::create([
          'purchase_id' => $purchase->id,
          'product_id' => $purchaseItem['product_id'],
          'quantity' => $purchaseItem['quantity'],
          'buying_price' => $purchaseItem['buying_price'],
          'subtotal' => $subtotal,
        ]);

        $totalAmount += $subtotal;

        // Add stock back
        $inventory = Inventory::where(
          'product_id',
          $purchaseItem['product_id']
        )->first();

        $inventory->quantity += $purchaseItem['quantity'];
        $inventory->last_stock_update = now();
        $inventory->save();

        // Update latest buying price
        $product = Product::find($purchaseItem['product_id']);

        $product->buying_price = $purchaseItem['buying_price'];
        $product->save();
      }

      // Update total amount
      $purchase->total_amount = $totalAmount;
      $purchase->save();

      return $purchase;
    });
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Purchase $purchase)
  {
    //Use Transaction
    DB::transaction(function () use ($purchase) {
      // Load purchase items
      $purchase->load('purchaseItems');

      // Reverse inventory
      foreach ($purchase->purchaseItems as $purchaseItem) {
        $inventory = Inventory::where('product_id', $purchaseItem->product_id)->first();

        $inventory->quantity -= $purchaseItem->quantity;
        $inventory->last_stock_update = now();
        $inventory->save();
      }

      //Delete purchase items
      $purchase->purchaseItems()->delete();

      //Delete purchase
      $purchase->delete();
    });
  }
}
