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
        Schema::create('product_sale', function (Blueprint $table) {
$table->id();
$table->string('name');
$table->unsignedInteger('product_id');
$table->decimal('price_sale', 12, 2);
$table->dateTime('date_begin');
$table->dateTime('date_end');
$table->dateTime('created_at');
$table->unsignedInteger('created_by')->default(1);
$table->dateTime('updated_at')->nullable();
$table->unsignedInteger('updated_by')->nullable();
$table->unsignedTinyInteger('status')->default(1);
});
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
