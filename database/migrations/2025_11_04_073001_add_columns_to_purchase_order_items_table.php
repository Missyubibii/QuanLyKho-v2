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
        Schema::table('purchase_order_items', function (Blueprint $table) {
            // Thêm các cột còn thiếu
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('price', 15, 2); // 15 chữ số tổng cộng, 2 chữ số thập phân
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            // Xóa các cột đã thêm nếu rollback migration
            $table->dropForeign(['purchase_order_id']);
            $table->dropForeign(['product_id']);
            $table->dropColumn(['purchase_order_id', 'product_id', 'quantity', 'price']);
        });
    }
};
