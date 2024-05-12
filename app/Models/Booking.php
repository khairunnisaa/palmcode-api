<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @OA\Schema(
 *     description="Booking model",
 *     title="Booking",
 *     required={"member_id", "country_id", "surfing_experience", "visit_date", "desired_board"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="The unique identifier for the booking",
 *         example="1"
 *     ),
 *     @OA\Property(
 *         property="member_id",
 *         type="integer",
 *         description="The ID of the member associated with the booking",
 *         example="1"
 *     ),
 *     @OA\Property(
 *         property="country_id",
 *         type="integer",
 *         description="The ID of the country associated with the booking",
 *         example="1"
 *     ),
 *     @OA\Property(
 *         property="id_verification_id",
 *         type="integer",
 *         description="The ID of the ID verification associated with the booking",
 *         example="1"
 *     ),
 *     @OA\Property(
 *         property="surfing_experience",
 *         type="string",
 *         description="The level of surfing experience of the member",
 *         example="Intermediate"
 *     ),
 *     @OA\Property(
 *         property="visit_date",
 *         type="string",
 *         format="date",
 *         description="The date of the visit",
 *         example="2024-05-15"
 *     ),
 *     @OA\Property(
 *         property="desired_board",
 *         type="string",
 *         description="The desired type of surfboard for the visit",
 *         example="Shortboard"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="The date and time when the booking was created",
 *         example="2024-05-15 10:00:00"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="The date and time when the booking was last updated",
 *         example="2024-05-15 10:30:00"
 *     )
 * )
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
