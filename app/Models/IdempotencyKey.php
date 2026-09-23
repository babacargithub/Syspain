<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

/**
 * @property int $user_id
 * @property string $idempotency_key
 * @property string $request_fingerprint
 * @property int|null $response_status_code
 * @property string|null $response_body
 */
class IdempotencyKey extends Model
{
    use Prunable;

    protected $fillable = [
        'user_id',
        'idempotency_key',
        'request_fingerprint',
        'response_status_code',
        'response_body',
    ];

    public function isStillProcessing(): bool
    {
        return $this->response_status_code === null;
    }

    // a key only needs to live as long as the client may retry the same submission
    public function prunable(): Builder
    {
        return static::where('created_at', '<', now()->subDays(2));
    }
}
