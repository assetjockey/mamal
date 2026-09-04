<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('plans') || Schema::hasColumn('plans', 'publicly_visible')) {
            return;
        }

        Schema::table('plans', function (Blueprint $table): void {
            $table->boolean('publicly_visible')->default(true)->after('featured')->index();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('plans') || ! Schema::hasColumn('plans', 'publicly_visible')) {
            return;
        }

        Schema::table('plans', function (Blueprint $table): void {
            $table->dropColumn('publicly_visible');
        });
    }
};
