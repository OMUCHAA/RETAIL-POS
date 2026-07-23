<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
	/**
	 * Display a listing of the resource.
	 */
	public function index(Request $request)
	{
		$customers = Customer::where('status', true)
			->when($request->search, function ($query) use ($request) {

				$query->where(function ($query) use ($request) {

					$query->where('customer_name', 'like', '%' . $request->search . '%')
						->orWhere('phone_number', 'like', '%' . $request->search . '%')
						->orWhere('email', 'like', '%' . $request->search . '%');
				});
			})
			->latest()
			->paginate(10)
			->withQueryString();

		return response()->json([
			'customers' => $customers,
		], 200);
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request)
	{
		//Validating data before storing
		$validated = $request->validate([
			'customer_name' => 'string|required|max:255',
			'phone_number' => 'string|required|max:20|unique:customer,phone_number',
			'email' => 'email|nullable|max:255',
			'address' => 'nullable|string|max:255',
		]);

		//Persit to the DB
		$customer = Customer::create([
			...$validated,
			'status'=> true,
		]);

		//Return the json response.
		return response()->json([
			'message' => 'Customer created successfully.',
			'customer' => $customer
		]);
	}

	/**
	 * Display the specified resource.
	 */
	public function show(Customer $customer)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, Customer $customer)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(Customer $customer)
	{
		//
	}
}
