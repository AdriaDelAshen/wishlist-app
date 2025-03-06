<?php

use App\Models\User;
use App\Models\Wishlist;
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
        Schema::create('wishlist_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->longText('description')->nullable();
            $table->longText('url_link')->nullable();
            $table->float('price')->nullable();
            $table->integer('priority');
            $table->boolean('is_bought')->default(false);
            $table->foreignIdFor(Wishlist::class);
            $table->foreignIdFor(User::class)->nullable();//User who will buy the item
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wishlist_items');
    }
};
