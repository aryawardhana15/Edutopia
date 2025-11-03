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
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->after('id');
            $table->enum('role', ['pelajar', 'mentor', 'admin'])->default('pelajar')->after('email');
            $table->string('photo')->nullable()->after('name');
            $table->boolean('is_active')->default(true)->after('role');
            $table->text('bio')->nullable()->after('photo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'role', 'photo', 'is_active', 'bio']);
        });
    }
};
