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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_code')->unique()->nullable(); // Thêm mã phiếu nhập tự động
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null'); // Liên kết nhà cung cấp
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Người tạo phiếu
            $table->date('order_date'); // Ngày đặt hàng
            $table->date('expected_date')->nullable(); // Ngày dự kiến nhận
            $table->enum('status', ['pending', 'processing', 'completed', 'cancelled'])->default('pending'); // Trạng thái (Thêm processing)
            $table->decimal('total_amount', 15, 2)->default(0); // Tổng tiền (tính toán)
            $table->text('notes')->nullable(); // Ghi chú
            $table->timestamps();
            $table->softDeletes(); //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
