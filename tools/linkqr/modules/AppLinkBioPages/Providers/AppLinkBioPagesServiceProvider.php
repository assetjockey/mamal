<?php

namespace Modules\AppLinkBioPages\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\AppLinkBio\Support\LinkBioAccess;

class AppLinkBioPagesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        register_user_sidebar_item('workspace', [
            'label' => 'Pages',
            'route_name' => 'portal.link-bio.index',
            'active_when' => ['portal.link-bio.index', 'portal.link-bio.edit'],
            'icon' => 'fa-light fa-link-horizontal',
            'order' => 10,
            'visible' => fn () => app(LinkBioAccess::class)->enabled(auth()->user()),
        ]);
    }
}
