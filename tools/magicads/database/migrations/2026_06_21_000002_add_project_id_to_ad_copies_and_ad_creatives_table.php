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
        if (! Schema::hasColumn('ad_copies', 'project_id')) {
            Schema::table('ad_copies', function (Blueprint $table) {
                $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
                $table->index(['project_id']);
            });
        }

        if (! Schema::hasColumn('ad_creatives', 'project_id')) {
            Schema::table('ad_creatives', function (Blueprint $table) {
                $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
                $table->index(['project_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('ad_copies', 'project_id')) {
            Schema::table('ad_copies', function (Blueprint $table) {
                $table->dropForeign(['project_id']);
                $table->dropIndex(['project_id']);
                $table->dropColumn('project_id');
            });
        }

        if (Schema::hasColumn('ad_creatives', 'project_id')) {
            Schema::table('ad_creatives', function (Blueprint $table) {
                $table->dropForeign(['project_id']);
                $table->dropIndex(['project_id']);
                $table->dropColumn('project_id');
            });
        }
    }
};
