<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // PostgreSQL: drop old check constraint and recreate with 'paid' added
        DB::statement('ALTER TABLE payrolls DROP CONSTRAINT IF EXISTS payrolls_status_check');
        DB::statement("ALTER TABLE payrolls ADD CONSTRAINT payrolls_status_check CHECK (status IN ('draft', 'finalized', 'paid'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE payrolls DROP CONSTRAINT IF EXISTS payrolls_status_check');
        DB::statement("ALTER TABLE payrolls ADD CONSTRAINT payrolls_status_check CHECK (status IN ('draft', 'finalized'))");
    }
};
