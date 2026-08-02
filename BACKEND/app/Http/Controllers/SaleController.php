<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;

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
   * Show the form for creating a new resource.
   */
  public function create()
  {
    //
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    //
  }

  /**
   * Display the specified resource.
   */
  public function show(Sale $sale)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(Sale $sale)
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
