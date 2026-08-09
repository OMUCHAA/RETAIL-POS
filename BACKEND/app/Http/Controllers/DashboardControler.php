<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Inventory;
use Illuminate\Http\Request;

class DashboardControler extends Controller
{
  public function index()
  {
    //Today's total sales data
    $todaySales = Sale::whereDate('sale_date', today())->sum('total_amount');

    //Today's total purchases
    $todayPurchases = Purchase::whereDate('purchase_date', today())->sum('total_amount');

    //Total Customers
    $totalCustomers = Customer::count();

    //Total products
    $totalProducts = Product::count();

    //Low satock
    $lowStockProducts = Inventory::join(
      'products',
      'inventories.product_id',
      '=',
      'products.id'
    )
      ->whereColumn(
        'inventories.quantity',
        '<',
        'products.minimum_stock'
      )
      ->select(
        'inventories.product_id',
        'inventories.quantity',
        'products.name',
        'products.minimum_stock'
      )
      ->get();

    $lowStockCount = $lowStockProducts->count();

    //Current stock value.
    $currentStockValue = Inventory::join(
      'products',
      'inventories.product_id',
      '=',
      'products.id'
    )
      ->selectRaw(
        'SUM(inventories.quantity * products.buying_price) as total'
      )
      ->value('total') ?? 0;

    //Recent sales.
    $recentSales = Sale::with('customer')->latest()->take(5)->get();

    return response()->json([
      'today_sales' => $todaySales,
      'today_purchases' => $todayPurchases,
      'total_customers' => $totalCustomers,
      'total_products' => $totalProducts,
      'low_stock_count' => $lowStockCount,
      'low_stock_products' => $lowStockProducts,
      'current_stock_value' => $currentStockValue,
      'recent_sales' => $recentSales,
    ], 200);
  }
}
