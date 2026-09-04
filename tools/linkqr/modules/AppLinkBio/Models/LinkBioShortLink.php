<?php

namespace Modules\AppLinkBio\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class LinkBioShortLink extends Model
{
    protected $fillable = [
        'link_bio_page_id',
        'owner_user_id',
        'code',
        'domain',
        'target_url',
        'is_active',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'link_bio_page_id' => 'integer',
            'owner_user_id' => 'integer',
            'is_active' => 'boolean',
            'settings' => 'array',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(LinkBioPage::class, 'link_bio_page_id');
    }

    public static function forPage(LinkBioPage $page): self
    {
        return self::query()->firstOrCreate(
            ['link_bio_page_id' => (int) $page->id],
            [
                'owner_user_id' => (int) $page->owner_user_id,
                'code' => self::uniqueCode($page),
                'target_url' => route('link-bio.public.show', ['slug' => $page->slug]),
                'is_active' => true,
            ]
        );
    }

    protected static function uniqueCode(LinkBioPage $page): string
    {
        $base = Str::lower(Str::slug((string) $page->slug, ''));
        $base = $base !== '' ? Str::limit($base, 18, '') : Str::lower(Str::random(8));
        $code = $base;
        $counter = 2;

        while (self::query()->where('code', $code)->exists()) {
            $code = Str::limit($base, 16, '').$counter;
            $counter++;
        }

        return $code;
    }
}
