<?php

use App\Models\Address;
use App\Models\Book;
use App\Models\User;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\post;

test('checkout reuses existing address if ID is provided', function () {
    $user = User::factory()->create();
    $address = Address::factory()->create([
        'user_id' => $user->id,
        'name' => 'Old Name',
        'address_details' => '123 Main St',
    ]);

    $book = Book::factory()->create();

    actingAs($user);

    // Initial count
    expect($user->addresses()->count())->toBe(1);

    $checkoutData = [
        'name' => 'New Name', // Changed name
        'addressId' => $address->id,
        'phone' => '01700000000',
        'divisionId' => 1,
        'divisionName' => 'Dhaka',
        'districtId' => 1,
        'districtName' => 'Dhaka',
        'upazilaId' => 1,
        'upazilaName' => 'Savar',
        'unionId' => 1,
        'unionName' => 'Aminbazar',
        'addressDetails' => '123 Main St Updated', // Changed details
        'paymentMethod' => 'cod',
        'cartItems' => [
            [
                'id' => $book->id,
                'name' => $book->name,
                'price' => 100,
                'quantity' => 1,
            ]
        ],
        'subtotal' => 100,
        'shippingCharge' => 50,
        'totalPrice' => 150,
    ];

    $response = post(route('checkout.store'), $checkoutData);
    $response->assertSessionHasNoErrors()
        ->assertRedirect();

    // Check that we still have only 1 address
    expect($user->fresh()->addresses()->count())->toBe(1);

    // Check that the address was updated
    $updatedAddress = $user->fresh()->addresses->first();
    expect($updatedAddress->name)->toBe('New Name');
    expect($updatedAddress->address_details)->toBe('123 Main St Updated');
});

test('checkout reuses existing address if DETAILS match exactly (fuzzy match)', function () {
    $user = User::factory()->create();
    $address = Address::factory()->create([
        'user_id' => $user->id,
        'name' => 'John Doe',
        'phone' => '01700000000',
        'address_details' => '123 Same St',
    ]);

    $book = Book::factory()->create();

    actingAs($user);

    expect($user->addresses()->count())->toBe(1);

    $checkoutData = [
        'name' => 'John Doe',
        'addressId' => null, // NO ID provided
        'phone' => '01700000000',
        'divisionId' => 1,
        'divisionName' => 'Dhaka',
        'districtId' => 1,
        'districtName' => 'Dhaka',
        'upazilaId' => 1,
        'upazilaName' => 'Savar',
        'unionId' => 1,
        'unionName' => 'Aminbazar',
        'addressDetails' => '123 Same St', // Same details
        'paymentMethod' => 'cod',
        'cartItems' => [
            [
                'id' => $book->id,
                'name' => $book->name,
                'price' => 100,
                'quantity' => 1,
            ]
        ],
        'subtotal' => 100,
        'shippingCharge' => 50,
        'totalPrice' => 150,
    ];

    post(route('checkout.store'), $checkoutData)->assertRedirect();

    // Should still be 1 because it matched attributes
    expect($user->fresh()->addresses()->count())->toBe(1);
});

test('checkout creates NEW address if DETAILS do not match', function () {
    $user = User::factory()->create();
    Address::factory()->create([
        'user_id' => $user->id,
        'name' => 'John Doe',
        'phone' => '01700000000',
        'address_details' => '123 Old St',
    ]);

    $book = Book::factory()->create();

    actingAs($user);

    expect($user->addresses()->count())->toBe(1);

    $checkoutData = [
        'name' => 'John Doe',
        'addressId' => null,
        'phone' => '01700000000',
        'divisionId' => 1,
        'divisionName' => 'Dhaka',
        'districtId' => 1,
        'districtName' => 'Dhaka',
        'upazilaId' => 1,
        'upazilaName' => 'Savar',
        'unionId' => 1,
        'unionName' => 'Aminbazar',
        'addressDetails' => '456 New St', // Different details
        'paymentMethod' => 'cod',
        'cartItems' => [
            [
                'id' => $book->id,
                'name' => $book->name,
                'price' => 100,
                'quantity' => 1,
            ]
        ],
        'subtotal' => 100,
        'shippingCharge' => 50,
        'totalPrice' => 150,
    ];

    post(route('checkout.store'), $checkoutData)->assertRedirect();

    // Should now be 2
    expect($user->fresh()->addresses()->count())->toBe(2);
});
