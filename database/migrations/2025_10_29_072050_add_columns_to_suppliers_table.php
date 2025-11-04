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
        Schema::table('suppliers', function (Blueprint $table) {
            // Thêm cột is_active để xác định nhà cung cấp còn hoạt động hay không
            $table->boolean('is_active')->default(true)->after('tax_code');

            // Thêm cột website cho nhà cung cấp
            $table->string('website')->nullable()->after('is_active');

            // Thêm cột notes để ghi chú thêm thông tin
            $table->text('notes')->nullable()->after('website');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'website', 'notes']);
        });
    }
};
