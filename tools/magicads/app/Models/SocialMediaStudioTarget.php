<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A single network delivery for a Social Media Studio post.
 */
class SocialMediaStudioTarget extends Model
{
    protected $table = 'social_media_studio_targets';

    protected $guarded = [];

    protected $casts = [
        'next_run_at'     => 'datetime',
        'last_attempt_at' => 'datetime',
        'published_at'    => 'datetime',
        'attempts'        => 'integer',
        'run_count'       => 'integer',
    ];

    public function post()
    {
        return $this->belongsTo(SocialMediaStudioPost::class, 'post_id');
    }

    public function account()
    {
        return $this->belongsTo(SocialMediaStudioAccount::class, 'account_id');
    }

    public function definition(): array
    {
        return config("social-media-studio.platforms.{$this->platform}", []);
    }

    public function label(): string
    {
        return $this->definition()['label'] ?? ucfirst(str_replace('_', ' ', $this->platform));
    }
}
