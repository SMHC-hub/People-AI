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
            DB::statement("ALTER TABLE attendances MODIFY COLUMN status VARCHAR(30) NOT NULL DEFAULT 'present'");
        } else {
            Schema::table('attendances', function (Blueprint $table) {
                $table->string('status', 30)->default('present')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE attendances MODIFY COLUMN status ENUM('present', 'late', 'absent') NOT NULL DEFAULT 'present'");
        }
    }
};
