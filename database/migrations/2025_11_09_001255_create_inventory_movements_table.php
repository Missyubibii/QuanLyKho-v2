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
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['in', 'out']);
            $table->integer('quantity_change'); // Số lượng thay đổi (dương cho nhập, âm cho xuất)
            $table->integer('quantity_after'); // Số lượng sau khi thay đổi
            $table->string('source_type'); // 'App\Models\PurchaseOrder' hoặc 'App\Models\SalesOrder'
            $table->unsignedBigInteger('source_id'); // ID của PO hoặc SO
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
