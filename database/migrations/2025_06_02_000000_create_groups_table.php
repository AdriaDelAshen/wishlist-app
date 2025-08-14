<?php

use App\Models\Group;
use App\Models\User;
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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_private')->default(true);
            $table->boolean('is_active')->default(true);
            $table->foreignIdFor(User::class);
            $table->timestamps();
        });

        Schema::create('group_user', function (Blueprint $table) {
            $table->string('role')->default('member');
            $table->boolean('can_invite_users')->default(false);
            $table->foreignIdFor(Group::class);
            $table->foreignIdFor(User::class);
            $table->timestamps();

            $table->primary(['group_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_user');
        Schema::dropIfExists('groups');
    }
};
