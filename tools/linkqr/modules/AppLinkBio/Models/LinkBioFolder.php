<?php

namespace Modules\AppLinkBio\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LinkBioFolder extends Model
{
    protected $fillable = [
        'owner_user_id',
        'team_id',
        'name',
        'color',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'owner_user_id' => 'integer',
            'team_id' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function pages(): HasMany
    {
        return $this->hasMany(LinkBioPage::class, 'folder_id');
    }
}
