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
        if (! Schema::hasTable('rag_documents')) {
            Schema::create('rag_documents', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('category')->default('policy'); // policy, handbook, compliance, benefits
                $table->string('file_path')->nullable();
                $table->string('file_type')->default('pdf'); // pdf, docx, txt, md
                $table->unsignedInteger('file_size_bytes')->default(0);
                $table->unsignedInteger('chunk_count')->default(0);
                $table->string('version')->default('1.0');
                $table->string('status')->default('indexed'); // pending, indexed, failed
                $table->string('allowed_roles')->default('all'); // all, hr_only, manager_up
                $table->unsignedBigInteger('department_id')->nullable();
                $table->unsignedBigInteger('tenant_id')->default(1);
                $table->timestamps();

                $table->index(['tenant_id', 'category']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rag_documents');
    }
};
