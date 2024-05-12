<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema()
 */
class IdVerification extends Model
{
    protected $fillable = ['member_id', 'file_name', 'link_url_path'];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
