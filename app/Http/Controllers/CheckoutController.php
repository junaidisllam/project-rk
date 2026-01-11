<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    public function index()
    {
        $addresses = auth()->check() ? auth()->user()->addresses()->latest()->get() : [];

        return Inertia::render('Checkout', [
            'addresses' => $addresses,
            'isLoggedIn' => auth()->check(),
            'user' => auth()->user(),
            'title' => 'চেকআউট | এলাকাডটকম',
            'description' => 'সহজ এবং নিরাপদ চেকআউট প্রক্রিয়ার মাধ্যমে আপনার বইগুলো অর্ডার সম্পন্ন করুন।',
        ]);
    }

    public function store(Request $request)
    {
        // Adjust validation rules based on auth status
        $rules = [
            'name' => 'required|string|max:255',
            'addressId' => 'nullable|integer|exists:addresses,id',
            'phone' => 'required|string|max:20',
            'altPhone' => 'nullable|string|max:20',
            'divisionId' => 'required|integer',
            'divisionName' => 'required|string',
            'districtId' => 'required|integer',
            'districtName' => 'required|string',
            'upazilaId' => 'required|integer',
            'upazilaName' => 'required|string',
            'unionId' => 'nullable|integer',
            'unionName' => 'nullable|string',
            'addressDetails' => 'required|string|max:500',
            'paymentMethod' => 'required|string|in:cod',
            'cartItems' => 'required|array|min:1',
            'cartItems.*.id' => 'required|integer|exists:books,id',
            'cartItems.*.name' => 'required|string',
            'cartItems.*.price' => 'required|numeric',
            'cartItems.*.quantity' => 'required|integer|min:1',
            'subtotal' => 'required|numeric',
            'shippingCharge' => 'required|numeric',
            'totalPrice' => 'required|numeric',
        ];

        if (!auth()->check()) {
            // Email is no longer required, we use Phone as Mobile
            // $rules['email'] = 'required|email|unique:users,email';
            $rules['password'] = 'required|string|min:8|confirmed';
            // Validate that this phone number is not already taken by another user
             $rules['phone'] = 'required|string|max:20|unique:users,mobile';
        }

        $validated = $request->validate($rules);

        DB::transaction(function () use ($validated, $request) {
            $user = auth()->user();

            // 1. Handle Guest Registration
            if (!$user) {
                $user = \App\Models\User::create([
                    'name' => $validated['name'],
                    // 'email' => $validated['email'], // Validation removed
                    'mobile' => $validated['phone'], // Use phone as mobile
                    'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
                ]);

                // Auto Login
                auth()->login($user);
            }

            // 2. Save Address (Always save for new address or guest)
            // If logged in and using existing address, frontend might send address_id but here we assume form submission means new/updated info for this order
            // The requirement says "save the address to their account".

            // Check if this exact address exists for user to avoid duplicate?
            // For simplicity and to fulfill "save address", we create a new address record.

            // 2. Save Address or Use Existing
            $address = null;

            if (!empty($validated['addressId'])) {
                $address = $user->addresses()->find($validated['addressId']);
                if ($address) {
                    // Update the existing address with any changes made in the form
                    $address->update([
                        'name' => $validated['name'],
                        'phone' => $validated['phone'],
                        'alt_phone' => $validated['altPhone'] ?? null,
                        'division_id' => $validated['divisionId'],
                        'division_name' => $validated['divisionName'],
                        'district_id' => $validated['districtId'],
                        'district_name' => $validated['districtName'],
                        'upazila_id' => $validated['upazilaId'],
                        'upazila_name' => $validated['upazilaName'],
                        'union_id' => $validated['unionId'] ?? null,
                        'union_name' => $validated['unionName'] ?? null,
                        'address_details' => $validated['addressDetails'],
                    ]);
                }
            }

            if (!$address) {
                // Fallback: Try to match similar address to avoid duplicate if user typed it again
                // We match on Name, Phone, and Address Details.
                $matchAttributes = [
                    'user_id' => $user->id,
                    'name' => trim($validated['name']),
                    'phone' => trim($validated['phone']),
                    'address_details' => trim($validated['addressDetails']),
                ];

                \Illuminate\Support\Facades\Log::info('Address Match Attributes:', $matchAttributes);

                $address = $user->addresses()->where($matchAttributes)->first();

                if (!$address) {
                    $address = $user->addresses()->create(array_merge($matchAttributes, [
                        'alt_phone' => $validated['altPhone'] ?? null,
                        'division_id' => $validated['divisionId'],
                        'division_name' => $validated['divisionName'],
                        'district_id' => $validated['districtId'],
                        'district_name' => $validated['districtName'],
                        'upazila_id' => $validated['upazilaId'],
                        'upazila_name' => $validated['upazilaName'],
                        'union_id' => $validated['unionId'] ?? null,
                        'union_name' => $validated['unionName'] ?? null,
                    ]));

                     // Ensure first address is default if not set
                    if (!$user->addresses()->where('is_default', true)->exists()) {
                        $address->update(['is_default' => true]);
                    }
                } else {
                     // Update existing match if needed (e.g. location IDs changed but text details same?)
                     // validating IDs might be needed but for now we assume match is good enough to reuse
                }
            }

            // 3. Create Order
            $order = Order::create([
                'user_id' => $user->id,
                'invoice_no' => 'INV-' . strtoupper(uniqid()),
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'alt_phone' => $validated['altPhone'] ?? null,
                'division_id' => $validated['divisionId'],
                'division_name' => $validated['divisionName'],
                'district_id' => $validated['districtId'],
                'district_name' => $validated['districtName'],
                'upazila_id' => $validated['upazilaId'],
                'upazila_name' => $validated['upazilaName'],
                'union_id' => $validated['unionId'] ?? null,
                'union_name' => $validated['unionName'] ?? null,
                'address_details' => $validated['addressDetails'],
                'subtotal' => $validated['subtotal'],
                'shipping_charge' => $validated['shippingCharge'],
                'total_price' => $validated['totalPrice'],
                'payment_method' => $validated['paymentMethod'],
                'payment_status' => 'pending',
                'status' => 'pending',
            ]);

            foreach ($validated['cartItems'] as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'book_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);
            }

            // Redirect needs to happen outside transaction return or we carry the order object out
            $this->createdOrder = $order;
        });

        return redirect()->route('order.success', [
            'orderId' => $this->createdOrder->id,
            'invoiceNo' => $this->createdOrder->invoice_no,
            'totalPrice' => $this->createdOrder->total_price,
        ]);
    }

    private $createdOrder;
}
