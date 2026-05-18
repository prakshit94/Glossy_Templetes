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
            'label'          => 'required|string|max:255',
            'status'         => 'required|in:active,inactive',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'village_id'     => 'nullable|exists:villages,id',
            'city'           => 'required|string|max:255',
            'state'          => 'required|string|max:255',
            'pincode'        => 'required|string|max:20',
            'is_default'     => 'nullable|boolean',
        ]);

        if ($request->has('is_default') && $request->is_default) {
            // Unset other default addresses for this customer
            $customer->addresses()->update(['is_default' => false]);
        }

        $address             = new PartyAddress($validated);
        $address->is_default = (bool) $request->has('is_default');
        $customer->addresses()->save($address);

        activity('customers')
            ->performedOn($address)
            ->causedBy(auth()->user())
            ->withProperties([
                'customer_id'    => $customer->id,
                'customer_name'  => $customer->name,
                'label'          => $address->label,
                'address_line_1' => $address->address_line_1,
            ])
            ->log("New address '{$address->label}' added for customer {$customer->name}");

        return back()->with('success', 'Address added successfully.');
    }

    public function update(Request $request, Customer $customer, PartyAddress $address)
    {
        // Ensure the address belongs to the customer
        if ($address->party_id !== $customer->id) {
            abort(403, 'This address does not belong to the specified customer.');
        }

        $validated = $request->validate([
            'label'          => 'required|string|max:255',
            'status'         => 'required|in:active,inactive',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'village_id'     => 'nullable|exists:villages,id',
            'city'           => 'required|string|max:255',
            'state'          => 'required|string|max:255',
            'pincode'        => 'required|string|max:20',
            'is_default'     => 'nullable|boolean',
        ]);

        if ($request->has('is_default') && $request->is_default) {
            // Unset other default addresses for this customer
            $customer->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $validated['is_default'] = (bool) $request->has('is_default');
        $address->update($validated);

        activity('customers')
            ->performedOn($address)
            ->causedBy(auth()->user())
            ->withProperties([
                'customer_id'   => $customer->id,
                'customer_name' => $customer->name,
                'label'         => $address->label,
            ])
            ->log("Address '{$address->label}' updated for customer {$customer->name}");

        return back()->with('success', 'Address updated successfully.');
    }

    public function destroy(Customer $customer, PartyAddress $address)
    {
        // Ensure the address belongs to the customer
        if ($address->party_id !== $customer->id) {
            abort(403, 'This address does not belong to the specified customer.');
        }

        $label = $address->label;

        $address->delete();

        activity('customers')
            ->causedBy(auth()->user())
            ->withProperties([
                'customer_id'   => $customer->id,
                'customer_name' => $customer->name,
                'label'         => $label,
            ])
            ->log("Address '{$label}' deleted for customer {$customer->name}");

        return back()->with('success', 'Address deleted successfully.');
    }
}
