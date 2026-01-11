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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('phone');
            $table->string('alt_phone')->nullable();
            $table->unsignedBigInteger('division_id');
            $table->string('division_name');
            $table->unsignedBigInteger('district_id');
            $table->string('district_name');
            $table->unsignedBigInteger('upazila_id');
            $table->string('upazila_name');
            $table->unsignedBigInteger('union_id')->nullable();
            $table->string('union_name')->nullable();
            $table->text('address_details');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};