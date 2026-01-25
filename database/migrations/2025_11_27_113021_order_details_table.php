<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('order_id');
            $table->unsignedInteger('product_id');
            $table->decimal('price', 12, 2);
            $table->unsignedInteger('qty');
            $table->decimal('amount', 12, 2);
            $table->integer('discount')->default(0);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
