<?php

namespace Modules\AppLinkBio\Support;

use Modules\AdminSettings\Support\OptionStore;
use Modules\AdminUser\Models\User;
use Modules\AppLinkBio\Models\LinkBioPage;
use Modules\AppTeams\Support\TeamWorkspaceAccess;

class LinkBioAccess
{
    public function enabled(?User $user): bool
    {
        return $user?->canUsePlanFeature('link_bio') ?? false;
    }

    public function workspaceOwnerUserId(?User $user): int
    {
        return TeamWorkspaceAccess::workspaceOwnerUserId($user);
    }

    public function currentTeamId(?User $user): ?int
    {
        return TeamWorkspaceAccess::activeTeam($user)?->id;
    }

    public function maxPages(?User $user): ?int
    {
        $limit = $user?->planLimit('max_link_bio_pages');

        if (! is_numeric($limit)) {
            return 1;
        }

        $value = (int) $limit;

        return $value < 0 ? null : $value;
    }

    public function pagesCount(?User $user): int
    {
        if (! $user) {
            return 0;
        }

        return LinkBioPage::query()
            ->ownedBy($this->workspaceOwnerUserId($user))
            ->count();
    }

    public function canCreate(?User $user): bool
    {
        if (! $this->enabled($user)) {
            return false;
        }

        $limit = $this->maxPages($user);

        if ($limit === null) {
            return true;
        }

        return $this->pagesCount($user) < $limit;
    }

    public function canCustomizeBranding(?User $user): bool
    {
        if (! $this->enabled($user)) {
            return false;
        }

        return ! $user?->plan || $user->hasPlanFeature('link_bio_remove_branding');
    }

    public function canUseAiBio(?User $user): bool
    {
        if (! $this->enabled($user)) {
            return false;
        }

        return ! $user?->plan || $user->hasPlanFeature('link_bio_ai_assistant');
    }

    public function canUseTemplates(?User $user): bool
    {
        if (! $this->enabled($user)) {
            return false;
        }

        return ! $user?->plan || $user->hasPlanFeature('link_bio_templates');
    }

    public function canUseAnalytics(?User $user): bool
    {
        if (! $this->enabled($user)) {
            return false;
        }

        return ! $user?->plan || $user->hasPlanFeature('link_bio_analytics');
    }

    public function canUseQrCodes(?User $user): bool
    {
        if (! $this->enabled($user)) {
            return false;
        }

        return ! $user?->plan || $user->hasPlanFeature('link_bio_qr_codes');
    }

    public function defaultBrandingText(): string
    {
        $value = trim((string) app(OptionStore::class)->get('link_bio_default_branding_text', 'Powered by Link Bio'));

        return $value !== '' ? $value : 'Powered by Link Bio';
    }

    public function defaultTemplateKey(): string
    {
        return LinkBioTemplateCatalog::find((string) app(OptionStore::class)->get(
            'link_bio_default_template',
            config('modules.applinkbio.default_template', 'aurora')
        ))['key'];
    }

    public function defaultStatus(): string
    {
        $status = (string) app(OptionStore::class)->get('link_bio_default_status', 'draft');

        return in_array($status, ['draft', 'published'], true) ? $status : 'draft';
    }

    public function defaultIsPublished(): bool
    {
        return $this->defaultStatus() === 'published';
    }
}
