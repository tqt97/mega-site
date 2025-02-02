<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

            $version = DB::selectOne('SELECT VERSION() as version')->version;

            preg_match('/(\d+\.\d+)/', $version, $matches);
            $numericVersion = isset($matches[1]) ? (float) $matches[1] : 0;

            if ($numericVersion >= 8.0 || str_contains($version, 'MariaDB') && $numericVersion >= 10.6) {
                // support for MySQL 8.0 and MariaDB 10.6 and up
                $table->index('check_in')->withStats();
                $table->index('check_out')->withStats();
            } else {
                $table->index('check_in');
                $table->index('check_out');
            }

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
