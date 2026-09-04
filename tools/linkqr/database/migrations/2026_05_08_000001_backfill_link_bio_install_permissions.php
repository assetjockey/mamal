<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('plans') || ! Schema::hasColumn('plans', 'permissions')) {
            return;
        }

        DB::table('plans')
            ->select(['id', 'slug', 'permissions'])
            ->orderBy('id')
            ->chunkById(100, function ($plans): void {
                foreach ($plans as $plan) {
                    $permissions = json_decode((string) $plan->permissions, true);

                    if (! is_array($permissions)) {
                        continue;
                    }

                    $updates = [
                        'link_bio' => true,
                        'max_link_bio_pages' => $this->maxPagesForPlan((string) ($plan->slug ?? '')),
                        'link_bio_templates' => true,
                        'link_bio_ai_assistant' => true,
                        'link_bio_analytics' => true,
                        'link_bio_ab_testing' => true,
                        'link_bio_qr_codes' => true,
                        'link_bio_remove_branding' => $this->removesBranding((string) ($plan->slug ?? '')),
                    ];

                    $changed = false;

                    foreach ($updates as $key => $value) {
                        if (($permissions[$key] ?? null) !== $value) {
                            $permissions[$key] = $value;
                            $changed = true;
                        }
                    }

                    if ($changed) {
                        DB::table('plans')
                            ->where('id', $plan->id)
                            ->update(['permissions' => json_encode($permissions)]);
                    }
                }
            }, 'id');
    }

    public function down(): void
    {
        //
    }

    protected function maxPagesForPlan(string $slug): int
    {
        return match (true) {
            str_contains($slug, 'agency') => -1,
            str_contains($slug, 'growth') => 25,
            default => 5,
        };
    }

    protected function removesBranding(string $slug): bool
    {
        return str_contains($slug, 'agency') || str_contains($slug, 'growth');
    }
};
