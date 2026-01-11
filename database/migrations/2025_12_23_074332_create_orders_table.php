<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // User info (Nullable for Guest Checkout)
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');

            // Tracking
            $table->string('invoice_no')->unique(); // যেমন: INV-1001

            // Customer Details (Snapshot from Form)
            $table->string('name');
            $table->string('phone');
            $table->string('alt_phone')->nullable();

            // Shipping Address
            $table->unsignedBigInteger('division_id')->nullable();
            $table->string('division_name');

            $table->unsignedBigInteger('district_id')->nullable();
            $table->string('district_name');

            $table->unsignedBigInteger('upazila_id')->nullable();
            $table->string('upazila_name');

            $table->unsignedBigInteger('union_id')->nullable();
            $table->string('union_name');

            $table->text('address_details'); // বিস্তারিত ঠিকানা

            // Financials
            $table->decimal('subtotal', 10, 2);
            $table->decimal('shipping_charge', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2);

            // Payment & Status
            $table->string('payment_method')->default('cod'); // cod, bkash, sslcommerz
            $table->string('payment_status')->default('pending'); // pending, paid
            $table->string('status')->default('pending'); // pending, processing, shipped, delivered, cancelled

            $table->text('note')->nullable(); // কাস্টমার নোট (যদি থাকে)

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
