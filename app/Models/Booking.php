<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @OA\Schema()
 */
class Booking extends Model
{
    protected $fillable = ['member_id', 'country_id','id_verification_id', 'surfing_experience', 'visit_date', 'desired_board'];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function idVerification(): HasOne
    {
        return $this->hasOne(IdVerification::class);
    }
}
