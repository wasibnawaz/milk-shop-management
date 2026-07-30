<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('cashier')->after('email');

            // Lets an owner revoke access without destroying the audit trail
            // of sales that user recorded.
            $table->boolean('is_active')->default(true)->after('role');

            $table->timestamp('last_login_at')->nullable()->after('remember_token');

            $table->softDeletes();

            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropColumn(['role', 'is_active', 'last_login_at', 'deleted_at']);
        });
    }
};
