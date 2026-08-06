<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Models\Sale;
use App\Models\Sale_Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $sales = Sale::with('customer')
      ->when($request->search, function ($query) use ($request) {

        $query->where('invoice_number', 'like', '%' . $request->search . '%')
          ->orWhereHas('customer', function ($query) use ($request) {

            $query->where('customer_name', 'like', '%' . $request->search . '%');
          });
      })
      ->when($request->payment_status, function ($query) use ($request) {

        $query->where('payment_status', $request->payment_status);
      })
      ->latest()
      ->paginate(10)
      ->withQueryString();

    return response()->json([
      'sales' => $sales
    ], 200);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'customer_id' => 'required|exists:customers,id',
      'user_id' => 'required|exists:users,id',
      'sale_date' => 'required|date',
      'invoice_number' => 'required|string|max:255|unique:sales,invoice_number',
      'payment_method' => 'required|in:cash,mpesa,card',
      'payment_status' => 'required|in:paid,pending,partial',
      'remarks' => 'nullable',

      'saleItems' => 'required|array|min:1',
      'saleItems.*.product_id' => 'required|exists:products,id',
      'saleItems.*.quantity' => 'required|integer|min:1',
    ]);

    foreach ($validated['saleItems'] as $saleItem) {
      $inventory = Inventory::with('product')->where('product_id', $saleItem->product_id)->first();

      if ($inventory->quantity < $saleItem['quantity']) {
        return response()->json([
          'message' => 'Insufficient stock for ' . $inventory->product->name,
        ], 422);
      }
    }

    $sale = DB::transaction(function () use ($validated) {
      $sale = Sale::create([
        'customer_id' => $validated['customer_id'],
        'user_id' => $validated['user_id'],
        'sale_date' => $validated['sale_date'],
        'invoice_number' => $validated['invoice_number'],
        'payment_method' => $validated['payment_method'],
        'payment_status' => $validated['payment_status'],
        'remarks' => $validated['remarks'],
        'total_amount' => 0
      ]);
      $totalAmount = 0;

      foreach ($validated['saleItems'] as $saleItem) {
        $inventory = Inventory::with('product')->where('product_id', $saleItem['product_id'])->first();

        $subtotal = $saleItem['quantity'] * $inventory->product->selling_price;

        Sale_Item::create([
          'sale_id' => $sale->id,
          'product_id' => $saleItem['product_id'],
          'quantity' => $saleItem['quantity'],
          'selling_price' => $inventory->product->selling_price,
          'subtotal' => $subtotal,
        ]);

        $totalAmount += $subtotal;

        $inventory->quantity -= $saleItem['quantity'];
        $inventory->last_stock_update = now();
        $inventory->save();
      }

      $sale->total_amount = $totalAmount;
      $sale->save();

      return $sale;
    });

    return response()->json([
      'sale' => $sale->load('cuatomer', 'saleItems.product'),
      'message' => 'Sale created successfully'
    ], 201);
  }

  /**
   * Display the specified resource.
   */
  public function show(Sale $sale)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, Sale $sale)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Sale $sale)
  {
    //
  }
}
