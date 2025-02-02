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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained()->onDelete('restrict');
            $table->foreignId('room_id')->nullable()->constrained()->onDelete('restrict');
            $table->foreignId('customer_id')->constrained()->onDelete('restrict');
            $table->integer('guests');
            $table->date('check_in');
            $table->date('check_out');
            $table->decimal('total_price', 10, 2);
            $table->timestamps();

            // Index the check_in and check_out columns
            $table->index('check_in');
            $table->index('check_out');

            // Index the check_in and check_out columns together
            $table->index(['check_in', 'check_out']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
