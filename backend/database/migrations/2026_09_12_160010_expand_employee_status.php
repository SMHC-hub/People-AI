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
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE employees MODIFY COLUMN status VARCHAR(30) NOT NULL DEFAULT 'active'");
        } else {
            Schema::table('employees', function (Blueprint $table) {
                $table->string('status', 30)->default('active')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE employees MODIFY COLUMN status ENUM('active', 'resigned', 'terminated') NOT NULL DEFAULT 'active'");
        }
    }
};
