<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\PartyAddress;
use Illuminate\Http\Request;

class CustomerAddressController extends Controller
{
    public function store(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'village_id' => 'nullable|exists:villages,id',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'pincode' => 'required|string|max:20',
            'is_default' => 'nullable|boolean',
        ]);

        if ($request->has('is_default') && $request->is_default) {
            // Unset other default addresses for this customer
            $customer->addresses()->update(['is_default' => false]);
        }

        $address = new PartyAddress($validated);
        $address->is_default = $request->has('is_default');
        $customer->addresses()->save($address);

        return back()->with('success', 'Address added successfully.');
    }

    public function update(Request $request, Customer $customer, PartyAddress $address)
    {
        // Ensure the address belongs to the customer
        if ($address->party_id !== $customer->id) {
            abort(403);
        }

        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'village_id' => 'nullable|exists:villages,id',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'pincode' => 'required|string|max:20',
            'is_default' => 'nullable|boolean',
        ]);

        if ($request->has('is_default') && $request->is_default) {
            // Unset other default addresses for this customer
            $customer->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $validated['is_default'] = $request->has('is_default');
        $address->update($validated);

        return back()->with('success', 'Address updated successfully.');
    }

    public function destroy(Customer $customer, PartyAddress $address)
    {
        // Ensure the address belongs to the customer
        if ($address->party_id !== $customer->id) {
            abort(403);
        }

        $address->delete();

        return back()->with('success', 'Address deleted successfully.');
    }
}
