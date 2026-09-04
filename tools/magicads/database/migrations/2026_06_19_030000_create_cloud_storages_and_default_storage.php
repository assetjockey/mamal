<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        if (! Schema::hasTable('cloud_storages')) {
            Schema::create('cloud_storages', function (Blueprint $table) {
                $table->id();
                $table->string('provider', 40)->unique();   // s3, wasabi, …
                $table->boolean('enabled')->default(false);  // offloading on + configured
                $table->text('config')->nullable();          // connection details (secret encrypted)
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('general_settings', 'default_storage')) {
            Schema::table('general_settings', function (Blueprint $table) {
                $table->string('default_storage', 40)->default('local');
            });
        }

    }

    public function down(): void
    {

        if (Schema::hasColumn('general_settings', 'default_storage')) {
            Schema::table('general_settings', function (Blueprint $table) {
                $table->dropColumn('default_storage');
            });
        }

        Schema::dropIfExists('cloud_storages');
    }

};
