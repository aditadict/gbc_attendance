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
        Schema::table('salary_components', function (Blueprint $table) {
            $table->string('name')->after('id');
            $table->string('code')->unique()->after('name');
            $table->enum('type', ['base', 'allowance', 'deduction'])->after('code');
            $table->boolean('is_taxable')->default(false)->after('type');
            $table->boolean('is_active')->default(true)->after('is_taxable');
            $table->text('description')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('salary_components', function (Blueprint $table) {
            $table->dropColumn(['name', 'code', 'type', 'is_taxable', 'is_active', 'description']);
        });
    }
};
