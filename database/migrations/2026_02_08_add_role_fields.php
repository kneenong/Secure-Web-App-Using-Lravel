// database/migrations/xxxx_add_user_profile_fields.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->text('address')->nullable()->after('phone');
            $table->string('profile_image')->nullable()->after('address');
            $table->date('date_of_birth')->nullable()->after('profile_image');
            $table->string('occupation')->nullable()->after('date_of_birth');
            $table->text('bio')->nullable()->after('occupation');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'address', 'profile_image', 'date_of_birth', 'occupation', 'bio']);
        });
    }
};