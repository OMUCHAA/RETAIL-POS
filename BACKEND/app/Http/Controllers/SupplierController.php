<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $suppliers = Supplier::latest()->paginate(10);

        return response()->json([
            'suppliers'=>$suppliers
        ], 200);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //validate
        $validated = $request->validate([
            'supplier_name' => 'string|required|max:255|unique:suppliers,supplier_name',
            'company_name' => 'required|string|max:255|unique:suppliers,company_name',
            'contact_phone' => 'string|required|max:20',
            'contact_email' => 'email|nullable|max:255',
            'address' => 'string|nullable|max:255',
            'status' => 'boolean',
            'remarks' => 'string|nullable'
        ]);

        $supplier = Supplier::create($validated);

        return response()->json([
            'message' => 'supplier crated successfully',
            'supplier' => $supplier
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier)
    {
        return response()->json([
            'supplier' => $supplier
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Supplier $supplier)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        //
    }
}
