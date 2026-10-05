<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('id')->constrained()->restrictOnDelete();
        });

        $timestamp = now();

        DB::table('roles')->insertOrIgnore([
            'name' => 'Patient / Client',
            'slug' => 'patient',
            'description' => 'A patient or spa client using the platform.',
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        $patientRoleId = DB::table('roles')->where('slug', 'patient')->value('id');

        DB::table('users')->whereNull('role_id')->update(['role_id' => $patientRoleId]);

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
        });
    }
};
