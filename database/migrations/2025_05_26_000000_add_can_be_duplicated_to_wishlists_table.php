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
        if (!Schema::hasColumn('wishlists', 'can_be_duplicated')) {
            Schema::table('wishlists', function (Blueprint $table) {
                $table->boolean('can_be_duplicated')->default(false)->after('expiration_date');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('wishlists', 'can_be_duplicated')) {
            Schema::table('wishlists', function (Blueprint $table) {
                $table->dropColumn('can_be_duplicated');
            });
        }
    }
};
