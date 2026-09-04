<?php

use App\Models\Extension;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Team plugin — schema + install registration.
 *
 * Ships with the "magicads-team" plugin. Mirrors the self-contained UGC Factory
 * / Avatar Studio install pattern: a single migration that
 *
 *   1. Creates the feature tables (teams, members, invitations, activity feed).
 *   2. Adds a per-project sharing pivot (project_user) so an owner can share an
 *      owned Project with team members at a viewer/editor access level.
 *   3. Adds the plugin's settings columns to the shared single-row
 *      extension_settings table (master switch + free-tier toggle).
 *   4. Adds the free-tier team-member limit to general_settings (the per-plan
 *      limit already lives on plans.team_members).
 *   5. Registers the marketplace `extensions` row as installed so
 *      HelperService::extensionTeam() can gate visibility.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ---- Teams (one per owner) ----
        if (! Schema::hasTable('teams')) {
            Schema::create('teams', function (Blueprint $table) {
                $table->id();
                $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
                $table->string('name', 120);
                $table->string('avatar')->nullable();
                $table->timestamps();

                // A user owns at most one team.
                $table->unique('owner_id');
            });
        }

        // ---- Memberships (a user belongs to at most one team) ----
        if (! Schema::hasTable('team_members')) {
            Schema::create('team_members', function (Blueprint $table) {
                $table->id();
                $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                // 'owner' | 'member'
                $table->string('role', 20)->default('member');
                $table->timestamp('joined_at')->nullable();
                $table->timestamps();

                // Global one-team-per-user rule.
                $table->unique('user_id');
                $table->index(['team_id', 'role']);
            });
        }

        // ---- Invitations (by email; supports not-yet-registered users) ----
        if (! Schema::hasTable('team_invitations')) {
            Schema::create('team_invitations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
                $table->string('email');
                $table->string('token', 64)->unique();
                $table->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete();
                // pending | accepted | declined | revoked | expired
                $table->string('status', 20)->default('pending');
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('accepted_at')->nullable();
                $table->timestamps();

                $table->index(['email', 'status']);
                $table->index(['team_id', 'status']);
            });
        }

        // ---- Activity feed (members' actions: consumption, transfers, …) ----
        if (! Schema::hasTable('team_activities')) {
            Schema::create('team_activities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
                // The member who performed the action (nullable for system rows).
                $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
                // credit.consume | credit.transfer | project.share | project.unshare |
                // member.join | member.leave | member.remove | invite.sent | invite.revoked
                $table->string('type', 40);
                $table->string('description', 255);
                // Credits involved (consumed or transferred); 0 when N/A.
                $table->integer('credits')->default(0);
                $table->json('meta')->nullable();
                $table->timestamps();

                $table->index(['team_id', 'created_at']);
                $table->index(['team_id', 'type']);
            });
        }

        // ---- Project sharing pivot ----
        if (! Schema::hasTable('project_user')) {
            Schema::create('project_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                // 'viewer' | 'editor'
                $table->string('access', 20)->default('viewer');
                $table->foreignId('shared_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['project_id', 'user_id']);
                $table->index('user_id');
            });
        }

        // ---- extension_settings columns ----
        Schema::table('extension_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('extension_settings', 'team_feature')) {
                $table->boolean('team_feature')->default(false);
            }
            if (! Schema::hasColumn('extension_settings', 'team_free_tier')) {
                $table->boolean('team_free_tier')->default(false);
            }
        });

        // ---- general_settings: free-tier team-member limit ----
        if (Schema::hasTable('general_settings') && ! Schema::hasColumn('general_settings', 'free_tier_team_members')) {
            Schema::table('general_settings', function (Blueprint $table) {
                $table->unsignedSmallInteger('free_tier_team_members')->nullable()->default(0);
            });
        }

    }

    public function down(): void
    {
        Schema::dropIfExists('project_user');
        Schema::dropIfExists('team_activities');
        Schema::dropIfExists('team_invitations');
        Schema::dropIfExists('team_members');
        Schema::dropIfExists('teams');

        Schema::table('extension_settings', function (Blueprint $table) {
            foreach (['team_feature', 'team_free_tier'] as $column) {
                if (Schema::hasColumn('extension_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        if (Schema::hasTable('general_settings') && Schema::hasColumn('general_settings', 'free_tier_team_members')) {
            Schema::table('general_settings', function (Blueprint $table) {
                $table->dropColumn('free_tier_team_members');
            });
        }

    }
};
