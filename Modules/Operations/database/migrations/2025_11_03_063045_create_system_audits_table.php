<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('system_audits', function (Blueprint $table) {
            $table->id();
            $table->enum('action', ['create', 'update', 'delete', 'access', 'export', 'import', 'error'])->default('access');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('module', 100)->nullable(); 
            $table->string('target_table', 100)->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('ip_address', 50)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->json('meta')->nullable(); 
            $table->timestamp('logged_at')->useCurrent();
            $table->timestamps();

            $table->index(['user_id', 'module', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_audits');
    }
};
