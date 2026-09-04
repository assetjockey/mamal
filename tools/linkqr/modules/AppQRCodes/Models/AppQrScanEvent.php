<?php

namespace Modules\AppQRCodes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppQrScanEvent extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'app_qr_code_id',
        'owner_user_id',
        'source',
        'ip_address',
        'user_agent',
        'country',
        'referer',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'app_qr_code_id' => 'integer',
            'owner_user_id' => 'integer',
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function qrCode(): BelongsTo
    {
        return $this->belongsTo(AppQrCode::class, 'app_qr_code_id');
    }
}
