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
        if (!Schema::hasColumn('wishlist_items', 'in_shopping_list')) {
            Schema::table('wishlist_items', function (Blueprint $table) {
                $table->boolean('in_shopping_list')->default(0)->after('priority');
            });
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('wishlist_items', 'in_shopping_list')) {
            Schema::table('wishlist_items', function (Blueprint $table) {
                $table->dropColumn('in_shopping_list');
            });
        }
    }
};
