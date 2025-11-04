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
        Schema::table('categories', function (Blueprint $table) {
            // Thêm cột is_active để xác định danh mục còn hoạt động hay không
            $table->boolean('is_active')->default(true)->after('description');

            // Thêm cột parent_id để tạo cấu trúc danh mục cha-con
            $table->unsignedBigInteger('parent_id')->nullable()->after('is_active');

            // Thêm cột color để phân biệt danh mục bằng màu sắc
            $table->string('color')->nullable()->after('parent_id');

            // Thêm cột icon để hiển thị icon cho danh mục
            $table->string('icon')->nullable()->after('color');

            // Thêm foreign key cho parent_id
            $table->foreign('parent_id')->references('id')->on('categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['is_active', 'parent_id', 'color', 'icon']);
        });
    }
};
