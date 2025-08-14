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
        if (!Schema::hasColumn('group_user', 'contribution_amount')) {
            Schema::table('group_user', function (Blueprint $table) {
                $table->decimal('contribution_amount')->default(0)->unsigned()->after('can_invite_users');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('group_user', 'contribution_amount')) {
            Schema::table('group_user', function (Blueprint $table) {
                $table->dropColumn('contribution_amount');
            });
        }
    }
};
