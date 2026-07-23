<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $suppliers = Supplier::when($request->search, function($query) use ($request) {
            $query->where('supplier_name', 'like', '%' . $request->search . '%')
            ->orWhere('company_name', 'like', '%' . $request->search . '%')
            ->orWhere('contact_phone', 'like', '%' . $request->search . '%')
            ->orWhere('company_email', 'like', '%' . $request->search . '%');
        })->latest()->paginate(10)->withQueryString();

        return response()->json([
            'suppliers' => $suppliers
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
        //Using a model binding method instesd of an id.
        return response()->json([
            'supplier' => $supplier
        ], 200);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        //Validate data
        $validated = $request->validate([
            'supplier_name' => 'string|required|max:255|unique:suppliers,supplier_name,' . $supplier->id,
            'company_name' => 'required|string|max:255|unique:suppliers,company_name,'. $supplier->id,
            'contact_phone' => 'string|required|max:20',
            'contact_email' => 'email|nullable|max:255',
            'address' => 'string|nullable|max:255',
            'status' => 'boolean',
            'remarks' => 'string|nullable'
        ]);

        //Persit data to the databse
        $supplier->update($validated);

        //return a json data response
        return response()->json([
            'message' => 'Supplier updated successfully',
            'supplier' => $supplier
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        //error handling incase supplier has a purchse history it should not be deleted.
        try {
            $supplier->delete();

            return response()->json([
                'message'=> 'supplier deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message'=> 'Supplier cannot be deleted because it has purchase records'
            ],409);
        }
    }
}
