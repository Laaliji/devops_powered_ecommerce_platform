<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('current_tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->string('mobile', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['current_tenant_id']);
            $table->dropColumn(['current_tenant_id', 'is_active', 'mobile']);
        });
    }
};
