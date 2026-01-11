<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use App\Models\Address;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

use Illuminate\Support\Facades\Gate;

class AddressController extends Controller
{
    public function index()
    {
        $addresses = Auth::user()->addresses()->latest()->get();

        return Inertia::render('Address/Index', [
            'addresses' => $addresses,
        ]);
    }

    public function store(StoreAddressRequest $request)
    {
        $address = Auth::user()->addresses()->create($request->validated());

        if ($request->is_default) {
            Auth::user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        } else if (Auth::user()->addresses()->count() === 1) {
            // If this is the only address, make it default
            $address->is_default = true;
            $address->save();
        }

        return Redirect::back()->with('success', 'Address created successfully.');
    }

    public function update(UpdateAddressRequest $request, Address $address)
    {
        Gate::authorize('update', $address);

        $address->update($request->validated());

        return Redirect::back()->with('success', 'Address updated successfully.');
    }

    public function destroy(Address $address)
    {
        Gate::authorize('delete', $address);

        $address->delete();

        return Redirect::back()->with('success', 'Address deleted successfully.');
    }

    public function setDefault(Address $address)
    {
        Gate::authorize('update', $address);

        Auth::user()->addresses()->update(['is_default' => false]);
        $address->is_default = true;
        $address->save();

        return Redirect::back()->with('success', 'Default address set successfully.');
    }
}
