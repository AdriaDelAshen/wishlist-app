<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\WishlistItemTypeEnum;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $hasType = Schema::hasColumn('wishlist_items', 'type');
        $hasGroupId = Schema::hasColumn('wishlist_items', 'group_id');

        if (! $hasType || ! $hasGroupId) {
            Schema::table('wishlist_items', function (Blueprint $table) use ($hasType, $hasGroupId) {
                if (! $hasType) {
                    $table->string('type')
                        ->default(WishlistItemTypeEnum::ONE_PERSON_GIFT->value)
                        ->after('price');
                }

                if (! $hasGroupId) {
                    $table->foreignId('group_id')
                        ->nullable()
                        ->after('user_id')
                        ->constrained()
                        ->onDelete('set null');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Use Doctrine to fetch foreign keys once
        $foreignKeys = Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableForeignKeys('wishlist_items');

        Schema::table('wishlist_items', function (Blueprint $table) use ($foreignKeys) {
            if (Schema::hasColumn('wishlist_items', 'group_id')) {
                foreach ($foreignKeys as $foreignKey) {
                    if (in_array('group_id', $foreignKey->getLocalColumns())) {
                        $table->dropForeign(['group_id']);
                        break;
                    }
                }
                $table->dropColumn('group_id');
            }

            if (Schema::hasColumn('wishlist_items', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};
